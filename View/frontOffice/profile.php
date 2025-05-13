<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';
session_start();

// Log file for debugging
$logFile = __DIR__ . '/../../debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    logMessage("User not logged in, redirecting to login");
    header("Location: login.php");
    exit;
}

$conn = config::getConnexion();
$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$user = $userC->getUserById($userId);

if (!$user) {
    logMessage("User not found for ID: $userId");
    header("Location: login.php?error=User not found");
    exit;
}

logMessage("User profile loaded for ID: $userId, photo_profil: " . ($user['photo_profil'] ?? 'NULL'));

// Handle the form submission for updating the profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $date_inscription = trim($_POST['date_inscription']);

    // Validate inputs
    if (empty($nom) || empty($prenom) || empty($email) || empty($date_inscription)) {
        header("Location: profile.php?error=All fields are required");
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: profile.php?error=Invalid email format");
        exit;
    }

    // Option 1: Email changes are disabled (email field is readonly)
    if ($email !== $user['email']) {
        header("Location: profile.php?error=Email cannot be changed");
        exit;
    }


    // Handle photo upload
    $photo_profil = $user['photo_profil'];
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            logMessage("Created upload directory: $uploadDir");
        }
        if (!is_writable($uploadDir)) {
            logMessage("Upload directory is not writable: $uploadDir");
            header("Location: profile.php?error=Upload directory is not writable");
            exit;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        $fileType = $_FILES['photo_profil']['type'];
        $fileSize = $_FILES['photo_profil']['size'];
        $fileTmpName = $_FILES['photo_profil']['tmp_name'];

        logMessage("Profile photo upload started: type=$fileType, size=$fileSize bytes");

        if (!in_array($fileType, $allowedTypes)) {
            logMessage("Profile photo upload failed: Invalid file type ($fileType)");
            header("Location: profile.php?error=Invalid file type. Only JPEG, PNG, and GIF are allowed");
            exit;
        }

        if ($fileSize > $maxFileSize) {
            logMessage("Profile photo upload failed: File size ($fileSize bytes) exceeds 5MB limit");
            header("Location: profile.php?error=File size exceeds 5MB limit");
            exit;
        }

        $fileExt = strtolower(pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION));
        $newFileName = $userId . '_' . time() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        logMessage("Attempting to move uploaded file to: $uploadPath");
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            logMessage("Profile photo upload failed: Unable to move file to $uploadPath");
            header("Location: profile.php?error=Failed to upload photo");
            exit;
        }

        try {
            $photo_profil = '' . $newFileName;
            $db = config::getConnexion();
            logMessage("Updating user with photo_profil: $photo_profil");
            $stmt = $db->prepare("UPDATE utilisateur SET photo_profil = :photo_profil WHERE id_utilisateur = :id_utilisateur");
            $stmt->execute([':photo_profil' => $photo_profil, ':id_utilisateur' => $userId]);
            logMessage("Photo uploaded and user updated with photo_profil: $photo_profil");
        } catch (Exception $e) {
            if ($uploadPath && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            logMessage("Error updating user with photo: " . $e->getMessage());
            header("Location: login.php?success=Registration successful! Please login. Note: Failed to save photo due to database error.");
            exit;
        }
        }
    

    // Update profile
    // try {
    //     $userC->updateUser($userId, $nom, $prenom, $email, $user['type'], $photo_profil, $date_inscription);
    // } catch (Exception $e) {
    //     logMessage("Error updating user: " . $e->getMessage());
    //     header("Location: profile.php?error=Error updating profile");
    //     exit;
    // }

    // Handle password update if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            header("Location: profile.php?error=Password must be at least 8 characters long");
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
        $stmt->execute([$hashed_password, $userId]); 
    }

    /*
    // Update last_email_change if email was changed (for Option 2)
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("UPDATE utilisateur SET last_email_change = NOW() WHERE id_utilisateur = ?");
        $stmt->execute([$userId]);
    }
    */

    logMessage("Profile updated successfully for user ID: $userId");
    header("Location: profile.php?success=Profile updated successfully");
    exit;
}


// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        // Delete the user's photo if it exists
        if ($user['photo_profil'] && file_exists(__DIR__ . '/../../' . $user['photo_profil'])) {
            unlink(__DIR__ . '/../../' . $user['photo_profil']);
            logMessage("Deleted profile picture: " . $user['photo_profil']);
        }
        $userC->deleteUser($userId);
        session_destroy();
        logMessage("User account deleted for ID: $userId");
        header("Location: login.php?success=Account deleted successfully");
        exit;
    } catch (Exception $e) {
        logMessage("Error deleting user: " . $e->getMessage());
        header("Location: profile.php?error=Error deleting account");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
    <style>
        .profile-pic-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
            display: block; /* Ensure the image is displayed */
        }
        .profile-pic-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #6c757d;
            border: 2px dashed #007bff;
        }
        .debug-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        body {
            background: linear-gradient(135deg, #8a63d2, #563d91);
        }
        .profile-section {
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            margin: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        /* Form field styling - Make more specific with !important to override global styles */
        .profile-section input[type="text"],
        .profile-section input[type="email"],
        .profile-section input[type="password"],
        .profile-section input[type="date"],
        .profile-section input[type="file"],
        .profile-section select {
            color: #333 !important; /* Force black text */
            background-color: white !important;
            border: 1px solid #ced4da !important;
        }
        
        /* Focus state styling */
        .profile-section input[type="text"]:focus,
        .profile-section input[type="email"]:focus,
        .profile-section input[type="password"]:focus,
        .profile-section input[type="date"]:focus,
        .profile-section input[type="file"]:focus,
        .profile-section select:focus {
            color: #333 !important;
            background-color: white !important;
            border-color: #6f42c1 !important;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25) !important;
        }
        
        /* Placeholder styling */
        .profile-section input::placeholder {
            color: #999 !important;
            opacity: 1 !important;
        }
        
        /* Disabled field styling */
        .profile-section input:disabled,
        .profile-section select:disabled {
            color: #333 !important;
            background-color: #f8f9fa !important;
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        /* Add the necessary navbar styles from homepage */
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
        
        main {
            padding-top: 100px;
        }
        
        @media (max-width: 768px) {
            header div {
                flex-direction: column;
            }
            
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            nav li {
                margin: 10px 0 !important;
            }
        }
    </style>
</head>
<body>
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
                    <li style="margin: 0 15px;"><a href="profile.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">My Profile</a></li>
                    
                    <?php if ($_SESSION['user_type'] === 'innovateur'): ?>
                        <!-- Innovateur-specific navigation -->
                        <li style="margin: 0 15px;"><a href="ContratInnovateur.php" style="color: white; text-decoration: none; font-weight: 500;">My Contracts</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurProjet.php" style="color: white; text-decoration: none; font-weight: 500;">My Projects</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurQuiz.html" style="color: white; text-decoration: none; font-weight: 500;">Innovator Quiz</a></li>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_type'] === 'investisseur'): ?>
                        <!-- Investisseur-specific navigation -->
                        <li style="margin: 0 15px;"><a href="ContratInvestisseur.php" style="color: white; text-decoration: none; font-weight: 500;">My Contracts</a></li>
                        <li style="margin: 0 15px;"><a href="Investisseur.php" style="color: white; text-decoration: none; font-weight: 500;">My Investments</a></li>
                        <li style="margin: 0 15px;"><a href="investisseurProjet.php" style="color: white; text-decoration: none; font-weight: 500;">Available Projects</a></li>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_type'] === 'administrateur'): ?>
                        <!-- Admin-specific navigation - links to backOffice -->
                        <li style="margin: 0 15px;"><a href="../backOffice/listeUser.php" style="color: white; text-decoration: none; font-weight: 500;">User Management</a></li>
                        <li style="margin: 0 15px;"><a href="../backOffice/ContratView.php" style="color: white; text-decoration: none; font-weight: 500;">Contracts Management</a></li>
                        <li style="margin: 0 15px;"><a href="../backOffice/FinancementView.php" style="color: white; text-decoration: none; font-weight: 500;">Financements Management</a></li>
                    <?php endif; ?>
                    
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="profile-section">
            <h2>Welcome, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>!</h2>
            <p class="slogan">You are logged in as <?php echo htmlspecialchars($user['type']); ?>. Manage your personal information below.</p>
            <?php if (isset($_GET['success'])): ?>
                <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <div class="profile-pic-container">
                <?php
                $imagePath = '';
                $defaultImage = '../../uploads/default_profile.jpg'; // Chemin relatif
                $debugMessage = '';
                if ($user['photo_profil']) {
                    $imagePath = '../../uploads/' . $user['photo_profil']; // Chemin relatif depuis frontOffice/
                    logMessage("Attempting to display profile picture: $imagePath");
                } else {
                    logMessage("No photo_profil set for user ID: $userId");
                    $debugMessage = "No profile picture set in the database.";
                }
                ?>
                <img src="<?php echo htmlspecialchars($imagePath ?: $defaultImage); ?>" alt="Profile Picture" class="profile-pic" onerror="console.log('Image failed to load: ' + this.src); this.onerror=null; this.src='<?php echo $defaultImage; ?>'; this.alt='Default Profile Picture';">
                <?php if ($debugMessage): ?>
                    <div class="debug-message"><?php echo htmlspecialchars($debugMessage); ?></div>
                <?php endif; ?>
            </div>
            <form method="POST" enctype="multipart/form-data" onsubmit="if (!validateForm()) return false; showLoader()">
                <div class="form-group input-with-icon">
                    <label for="photo_profil">Profile Picture</label>
                    <i class="fas fa-camera"></i>
                    <input type="file" id="photo_profil" name="photo_profil" accept="image/*" onchange="previewImage(event)">
                    <img id="photo-preview" style="display: none; width: 100px; height: 100px; margin-top: 10px; border-radius: 50%;">
                </div>
                <div class="form-group input-with-icon">
                    <label for="nom">Last Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" oninput="validateName('nom')">
                    <span id="nom-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="prenom">First Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" oninput="validateName('prenom')">
                    <span id="prenom-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    <span id="email-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter new password" oninput="validatePassword()">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    <span id="password-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="date_inscription">Registration Date</label>
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="date_inscription" name="date_inscription" value="<?php echo htmlspecialchars($user['date_inscription']); ?>" oninput="validateDate()">
                    <span id="date_inscription-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="type">Type</label>
                    <i class="fas fa-user-tag"></i>
                    <input type="text" id="type" value="<?php echo htmlspecialchars($user['type']); ?>" disabled>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" name="update" class="btn-primary">Update</button>
                    <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</button>
                    <a href="profile.php" class="btn-danger">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <footer style="width: 100%; background: linear-gradient(135deg, #6f42c1, #6610f2); color: white; padding: 2rem 0; text-align: center; margin-top: auto;">
        <div style="width: 100%; padding: 0 3rem;">
            <p>© 2025 InnoConnect. All rights reserved.</p>
        </div>
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

        function validatePassword() {
            const password = document.getElementById("password").value;
            const passwordError = document.getElementById("password-error");
            if (password.length > 0 && password.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                passwordError.style.display = "block";
            } else {
                passwordError.style.display = "none";
            }
        }

        function validateDate() {
            const date = document.getElementById("date_inscription").value;
            const dateError = document.getElementById("date_inscription-error");
            if (!date) {
                dateError.textContent = "Registration date is required.";
                dateError.style.display = "block";
            } else {
                dateError.style.display = "none";
            }
        }

        function validateForm() {
            const nomValid = validateName("nom");
            const prenomValid = validateName("prenom");
            validateEmail();
            validatePassword();
            validateDate();
            const emailError = document.getElementById("email-error").style.display === "block";
            const passwordError = document.getElementById("password-error").style.display === "block";
            const dateError = document.getElementById("date_inscription-error").style.display === "block";
            return nomValid && prenomValid && !emailError && !passwordError && !dateError;
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

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }
    </script>
</body>
</html>