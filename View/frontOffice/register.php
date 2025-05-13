<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use TwoCaptcha\TwoCaptcha;

session_start();

// Vérifier si la session est bien démarrée
if (session_status() !== PHP_SESSION_ACTIVE || session_id() === '') {
    logMessage("Session failed to start properly");
    die("Session error. Please clear your cookies and try again.");
}

// Régénérer l'ID de session pour éviter les problèmes de corruption
if (!isset($_SESSION['session_initialized'])) {
    session_regenerate_id(true);
    $_SESSION['session_initialized'] = true;
    logMessage("Session ID regenerated");
}

// Générer un jeton CSRF uniquement s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    logMessage("New CSRF token generated: " . $_SESSION['csrf_token']);
}

// Initialiser le compteur de tentatives CSRF
if (!isset($_SESSION['csrf_attempts'])) {
    $_SESSION['csrf_attempts'] = 0;
}

// Initialiser les notifications
$notification = isset($_SESSION['notification']) ? $_SESSION['notification'] : null;
unset($_SESSION['notification']);

// Fonction IA pour générer un message
function generateWelcomeMessage($name, $type) {
    $greetings = [
        'investisseur' => "Welcome to the investment world, ",
        'innovateur' => "Get ready to innovate, ",
        'administrateur' => "Take charge as an admin, "
    ];
    $greeting = $greetings[$type] ?? "Welcome, ";
    return $greeting . "$name! We're excited to have you on board at InnoConnect. Explore your new features and shape the future!";
}

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log file for debugging
$logFile = dirname(__DIR__, 2) . '/debug.log';
function logMessage($message) {
    global $logFile;
    if (empty($logFile)) {
        error_log("Log file path is empty in logMessage()");
        return;
    }
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "Log file created\n");
    }
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logMessage("Starting registration process");
    logMessage("CSRF token in session: " . ($_SESSION['csrf_token'] ?? 'undefined'));
    logMessage("CSRF token from POST: " . ($_POST['csrf_token'] ?? 'undefined'));

    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['csrf_attempts']++;
        logMessage("CSRF token validation failed (attempt " . $_SESSION['csrf_attempts'] . ")");
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        logMessage("CSRF token regenerated after failure: " . $_SESSION['csrf_token']);
        $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Invalid CSRF token. Please try again.'];
    } else {
        logMessage("CSRF token validated successfully");
        $_SESSION['csrf_attempts'] = 0;

        // Vérifier le reCAPTCHA
        $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        if (empty($recaptchaResponse)) {
            logMessage("reCAPTCHA validation failed: No response received");
            $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Please complete the reCAPTCHA verification'];
        } else {
            logMessage("reCAPTCHA response received: " . $recaptchaResponse);
            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
            $response = file_get_contents($verifyUrl . '?secret=' . '6LeZBisrAAAAAK2CRpum3u6w4B9egsPUWOJnKwv7' . '&response=' . $recaptchaResponse);
            $responseKeys = json_decode($response, true);
            logMessage("reCAPTCHA server response: " . json_encode($responseKeys));

            if (intval($responseKeys["success"]) !== 1) {
                logMessage("reCAPTCHA verification failed: " . json_encode($responseKeys));
                $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Failed reCAPTCHA verification'];
            } else {
                logMessage("reCAPTCHA verified successfully");
                // Validation des champs
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $email = trim($_POST['email']);
                $mot_de_passe = trim($_POST['mot_de_passe']);
                $type = $_POST['type'];

                if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($type)) {
                    logMessage("Validation failed: Missing required fields - nom: '$nom', prenom: '$prenom', email: '$email', type: '$type'");
                    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'All fields are required'];
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    logMessage("Validation failed: Invalid email format - email: '$email'");
                    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Invalid email format'];
                } elseif (!in_array($type, ['investisseur', 'innovateur', 'administrateur'])) {
                    logMessage("Validation failed: Invalid user type - type: '$type'");
                    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Invalid user type'];
                } else {
                    logMessage("Fields validated successfully");
                    $userC = new userC();
                    $existingUser = $userC->emailExists($email);
                    if ($existingUser) {
                        logMessage("Validation failed: Email already exists - email: '$email'");
                        $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Email already exists'];
                    } else {
                        logMessage("Email validated, proceeding to registration");
                        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                        $date_inscription = date('Y-m-d H:i:s');
                        $photo_profil = null;

                        try {
                            logMessage("Attempting to add user: nom=$nom, email=$email");
                            $db = config::getConnexion();
                            $db->beginTransaction();
                            $userC->ajouterUser($nom, $prenom, $email, $hashed_password, $type, $date_inscription, $photo_profil);
                            $userId = $db->lastInsertId();
                            logMessage("User added with ID: $userId");
                            $db->commit();
                            logMessage("Transaction committed");

                            // Gestion de la photo
                            if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                                $uploadDir = __DIR__ . '/../../uploads/';
                                logMessage("Upload dir: $uploadDir");
                                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                                $maxFileSize = 5 * 1024 * 1024;

                                $fileType = $_FILES['photo_profil']['type'];
                                $fileSize = $_FILES['photo_profil']['size'];
                                $fileTmpName = $_FILES['photo_profil']['tmp_name'];

                                if (!in_array($fileType, $allowedTypes) || $fileSize > $maxFileSize || !is_dir($uploadDir) || !is_writable($uploadDir)) {
                                    logMessage("Photo validation failed: type=$fileType, size=$fileSize, dir=$uploadDir");
                                    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Invalid file type, size exceeds 5MB, or server error.'];
                                } else {
                                    logMessage("Photo validated successfully");
                                    $fileExt = strtolower(pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION));
                                    $newFileName = $userId . '_' . time() . '.' . $fileExt;
                                    $uploadPath = $uploadDir . $newFileName;

                                    if (move_uploaded_file($fileTmpName, $uploadPath)) {
                                        logMessage("Photo uploaded to: $uploadPath");
                                        $photo_profil = $newFileName;
                                        $stmt = $db->prepare("UPDATE utilisateur SET photo_profil = :photo_profil WHERE id_utilisateur = :id_utilisateur");
                                        $stmt->execute([':photo_profil' => $photo_profil, ':id_utilisateur' => $userId]);

                                        // Génération de l'encodage facial
                                        $python_script = __DIR__ . '/../../scripts/generate_encoding.py';
                                        $command = escapeshellcmd('python ' . $python_script . ' ' . $uploadPath . ' ' . $userId);
                                        $output = shell_exec($command . ' 2>&1');
                                        logMessage("Encoding result: $output");

                                        if (strpos($output, "Aucun visage détecté") !== false || strpos($output, "Plusieurs visages détectés") !== false) {
                                            unlink($uploadPath);
                                            $db->prepare("UPDATE utilisateur SET photo_profil = NULL WHERE id_utilisateur = :id_utilisateur")->execute([':id_utilisateur' => $userId]);
                                            $_SESSION['notification'] = ['type' => 'danger', 'message' => strpos($output, "Aucun visage détecté") !== false ? "No face detected" : "Multiple faces detected"];
                                        }
                                    } else {
                                        logMessage("Failed to move uploaded file to $uploadPath");
                                        $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Failed to upload photo.'];
                                    }
                                }
                            }

                            // Générer et stocker le message de bienvenue
                            if (!isset($_SESSION['notification'])) {
                                $welcomeMessage = generateWelcomeMessage($prenom, $type);
                                $_SESSION['welcome_message'] = $welcomeMessage;
                                logMessage("Registration completed successfully with welcome message: $welcomeMessage");
                                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Registration successful! Please login.'];
                                header("Location: login.php");
                                exit;
                            }
                        } catch (Exception $e) {
                            $db->rollBack();
                            logMessage("Error during registration: " . $e->getMessage());
                            $_SESSION['notification'] = ['type' => 'danger', 'message' => $e->getMessage()];
                        }
                    }
                }
            }
        }
    }

    // Rediriger si une notification d'erreur est présente
    if (isset($_SESSION['notification']) && $_SESSION['notification']['type'] === 'danger') {
        logMessage("Redirecting to register.php with notification: " . $_SESSION['notification']['message']);
        header("Location: register.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="../assets2/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets2/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets2/css/bootstrap.min.css" rel="stylesheet" />
    <link id="pagestyle" href="../assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <link href="../../styles.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .strength-message {
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        .error-message { color: #dc3545; font-size: 0.85em; margin-top: 5px; display: none; }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 300px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: none;
        }
        .notification.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .notification.danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        /* Additional navbar styles to match homepage */
        .header {
            color: #ffffff;
            padding: 20px 0;
            transition: all 0.5s;
            z-index: 997;
        }
        
        .header .logo {
            line-height: 1;
        }
        
        .header .logo img {
            max-height: 36px;
            margin-right: 8px;
        }
        
        .header .logo h1 {
            font-size: 30px;
            margin: 0;
            font-weight: 700;
            color: #ffffff;
        }
        
        .navmenu ul {
            margin: 0;
            padding: 0;
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        .navmenu li {
            position: relative;
        }
        
        .navmenu > ul > li {
            padding: 10px 0 10px 24px;
            white-space: nowrap;
        }
        
        .navmenu a,
        .navmenu a:focus {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 3px;
            font-size: 15px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            white-space: nowrap;
            transition: 0.3s;
            position: relative;
        }
        
        .navmenu a i,
        .navmenu a:focus i {
            font-size: 12px;
            line-height: 0;
            margin-left: 5px;
        }
        
        .navmenu li:hover > a,
        .navmenu .active,
        .navmenu .active:focus,
        .navmenu li:hover > a:focus {
            color: #fff;
        }
        
        .container {
            margin-top: 120px;
            padding-top: 30px;
        }
        
        @media (max-width: 1200px) {
            .header {
                padding: 15px;
            }
            
            .header .logo {
                order: 1;
            }
            
            .mobile-nav-toggle {
                color: #fff;
                font-size: 28px;
                cursor: pointer;
                display: block;
                line-height: 0;
                transition: 0.5s;
                z-index: 9999;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>

    <div class="loader" id="loader"></div>

    <header style="background-color: #6f42c1; padding: 15px 20px; width: 100%; position: fixed; top: 0; z-index: 1000;">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; align-items: center;">
                <img src="../../innoconnect.jpeg" alt="InnoConnect Logo" style="width: 40px; height: 40px;">
                <h1 style="color: white; margin: 0 0 0 15px; font-size: 24px;">InnoConnect</h1>
            </div>
            <nav style="display: flex;">
                <ul style="display: flex; list-style: none; margin: 0; padding: 0;">
                    <li style="margin: 0 15px;"><a href="../../index.html" style="color: white; text-decoration: none; font-weight: 500;">Home</a></li>
                    <li style="margin: 0 15px;"><a href="register.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">Sign Up</a></li>
                    <li style="margin: 0 15px;"><a href="login.php" style="color: white; text-decoration: none; font-weight: 500;">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 register-container">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Sign Up</h2>
                        <p class="slogan text-center mb-4">Join InnoConnect and Shape the Future of Innovation!</p>

                        <!-- Affichage des notifications -->
                        <?php if ($notification): ?>
                            <div class="notification <?php echo $notification['type']; ?>" id="notification">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <script>
                                document.getElementById('notification').style.display = 'block';
                                setTimeout(() => {
                                    document.getElementById('notification').style.display = 'none';
                                }, 5000);
                            </script>
                        <?php endif; ?>

                        <?php
                        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            logMessage("CSRF token regenerated before form display: " . $_SESSION['csrf_token']);
                        }
                        ?>

                        <form method="POST" enctype="multipart/form-data" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <input type="text" style="display:none" name="fake-username">
                            <input type="password" style="display:none" name="fake-password">

                            <div class="form-group input-with-icon">
                                <label for="nom">Last Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="nom" name="nom" placeholder="Last Name" autocomplete="off" oninput="validateName('nom')">
                                <span id="nom-error" class="error-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="prenom">First Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="prenom" name="prenom" placeholder="First Name" autocomplete="off" oninput="validateName('prenom')">
                                <span id="prenom-error" class="error-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="email">Email</label>
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Email" oninput="validateEmail()" autocomplete="off">
                                <span id="email-error" class="error-message" style="display: none;"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="mot_de_passe">Password</label>
                                <i class="fas fa-lock"></i>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" autocomplete="off" oninput="checkPasswordStrength()">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
                                <span id="password-strength" class="strength-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="type">Type</label>
                                <i class="fas fa-user-tag"></i>
                                <select id="type" name="type" autocomplete="off">
                                    <option value="" disabled selected>Type</option>
                                    <option value="investisseur">Investor</option>
                                    <option value="innovateur">Innovator</option>
                                    <option value="administrateur">Administrator</option>
                                </select>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="photo_profil">Profile Picture (Required)</label>
                                <i class="fas fa-camera"></i>
                                <input type="file" id="photo_profil" name="photo_profil" accept="image/*" onchange="previewImage(event)">
                                <img id="photo-preview" style="display: none; width: 100px; height: 100px; margin-top: 10px; border-radius: 50%;">
                            </div>
                            <div class="form-group">
                                <label>Human Verification</label>
                                <div class="g-recaptcha" data-sitekey="6LeZBisrAAAAAFWGxjGV-ZR8JVq-N_rOlyqptgZe"></div>
                                <span id="recaptcha-error" class="error-message"></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <button type="submit" class="register-btn btn-primary">Sign Up</button>
                                <a href="../../index.html" class="btn-danger">Back</a>
                            </div>
                            <div>
                                <a href="forgot_password.php">Forgot Password?</a>
                            </div>
                        </form>
                        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 InnoConnect. All rights reserved.</p>
    </footer>

    <script>
        function validateName(fieldId) {
            const field = document.getElementById(fieldId);
            const value = field.value.trim();
            const errorElement = document.getElementById(`${fieldId}-error`);
            const nameRegex = /^[a-zA-Z\s]+$/;

            if (value === "") {
                errorElement.textContent = fieldId === "nom" ? "Last name is required." : "First name is required.";
                errorElement.style.display = "block";
                return false;
            } else if (!nameRegex.test(value)) {
                errorElement.textContent = fieldId === "nom" ? "Last name must contain only letters and spaces." : "First name must contain only letters and spaces.";
                errorElement.style.display = "block";
                return false;
            } else {
                errorElement.style.display = "none";
                return true;
            }
        }

        function validateEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("email-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
            } else {
                emailError.style.display = "none";
            }
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        function previewImage(event) {
            const preview = document.getElementById('photo-preview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById("mot_de_passe").value;
            const strengthMessage = document.getElementById("password-strength");
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (password.length === 0) {
                strengthMessage.textContent = "";
            } else if (strength <= 2) {
                strengthMessage.textContent = "Weak";
                strengthMessage.className = "strength-message strength-weak";
            } else if (strength === 3) {
                strengthMessage.textContent = "Medium";
                strengthMessage.className = "strength-message strength-medium";
            } else {
                strengthMessage.textContent = "Strong";
                strengthMessage.className = "strength-message strength-strong";
            }
        }

        function validateForm() {
            const nom = document.getElementById("nom").value;
            const prenom = document.getElementById("prenom").value;
            const email = document.getElementById("email").value;
            const mot_de_passe = document.getElementById("mot_de_passe").value;
            const type = document.getElementById("type").value;
            const emailError = document.getElementById("email-error");
            const nomError = document.getElementById("nom-error");
            const prenomError = document.getElementById("prenom-error");
            const recaptchaError = document.getElementById("recaptcha-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const recaptchaResponse = grecaptcha.getResponse();

            let isValid = true;

            if (!validateName("nom")) isValid = false;
            if (!validateName("prenom")) isValid = false;
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else emailError.style.display = "none";
            if (mot_de_passe.length < 8) {
                alert("Password must be at least 8 characters long.");
                isValid = false;
            }
            if (type === "") {
                alert("Please select a user type.");
                isValid = false;
            }
            if (!recaptchaResponse) {
                recaptchaError.textContent = "Please complete the reCAPTCHA verification.";
                recaptchaError.style.display = "block";
                isValid = false;
            } else recaptchaError.style.display = "none";

            return isValid;
        }

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }

        window.onload = function() {
            document.getElementById('nom').value = '';
            document.getElementById('prenom').value = '';
            document.getElementById('email').value = '';
            document.getElementById('mot_de_passe').value = '';
            document.getElementById('type').value = '';
        };
    </script>
</body>
</html>