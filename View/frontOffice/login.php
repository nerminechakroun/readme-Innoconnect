<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';
session_start();
$notification = isset($_SESSION['notification']) ? $_SESSION['notification'] : null;
$welcomeMessage = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : null;
unset($_SESSION['notification']);
unset($_SESSION['welcome_message']); // Effacer après affichage
// Générer un jeton CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php?error=Invalid CSRF token");
        exit;
    }

    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Server-side validation
    if (empty($email) || empty($mot_de_passe)) {
        header("Location: login.php?error=Email and password are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=Invalid email format");
        exit;
    }

    $userC = new userC();
    if (method_exists($userC, 'connexionUser')) {
        $user = $userC->connexionUser($email, $mot_de_passe);

        if ($user) {
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['user_type'] = $user['type']; // Stocker le type d'utilisateur dans la session
            // Rediriger tous les utilisateurs vers profile.php
            header("Location: profile.php");
            exit;
        } else {
            header("Location: login.php?error=Incorrect email or password");
            exit;
        }
    } else {
        header("Location: login.php?error=Login method not defined");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
</head>
<body>
    <div class="loader" id="loader"></div>
    <header style="background-color: #6f42c1;">
        <div class="logo">
            <img src="../../innoconnect.jpeg" alt="InnoConnect Logo" style="width: 50px; margin-right: 10px;">
            <span style="color: white;">InnoConnect</span>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php" class="active">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="register-container">
        <h2>Login</h2>
        <p class="slogan">Join InnoConnect and Shape the Future of Innovation!</p>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <form method="POST" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" style="display:none" name="fake-username">
            <input type="password" style="display:none" name="fake-password">
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

                        <!-- Affichage du message de bienvenue -->
                        <?php if ($welcomeMessage): ?>
                            <div style="margin-top: 20px; padding: 10px; background-color: #e9ecef; border-radius: 5px; text-align: center;">
                                <?php echo htmlspecialchars($welcomeMessage); ?>
                            </div>
                        <?php endif; ?>
            <div class="form-group input-with-icon">
                <label for="email">Email</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" oninput="validateEmail()" autocomplete="off">
                <span id="email-error" class="error-message" style="display: none;"></span>
            </div>
            <div class="form-group input-with-icon">
                <label for="mot_de_passe">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" oninput="validatePassword()" autocomplete="off">
                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
                <span id="password-error" class="error-message" style="display: none;"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <button type="submit" class="register-btn btn-primary">Login</button>
                <a href="../../index.html" class="btn-danger">Back</a>
            </div>
            <div>
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <div class="form-group">
    <button type="button" id="face-login-btn" class="btn btn-secondary">Login with Face Recognition</button>
</div>

<video id="video" width="320" height="250" autoplay style="display: block;"></video>
    <button onclick="captureImage()">Capturer et Se Connecter</button>
    <input type="hidden" id="webcam_image_input">
    <div id="face-login-message" style="margin-top: 10px; color: red;"></div>
<canvas id="canvas" width="320" height="240" style="display:none;"></canvas>

<script>
document.getElementById('face-login-btn').addEventListener('click', async () => {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    video.style.display = 'block';

    setTimeout(() => {
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = canvas.toDataURL('image/jpeg');

        stream.getTracks().forEach(track => track.stop());
        video.style.display = 'none';

        fetch('face_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: imageData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                alert('Face recognition failed: ' + data.error);
            }
        });
    }, 3000);
});
</script>
        </form>
        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>

    <footer>
        <p>© 2025 InnoConnect. All rights reserved.</p>
    </footer>

    <script>
        const video = document.getElementById('video');
        const webcamInput = document.getElementById('webcam_image_input');
        const messageDiv = document.getElementById('face-login-message');

        // Accéder à la webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                messageDiv.textContent = 'Erreur : Accès à la webcam refusé ou non disponible. Vérifiez les permissions.';
                console.error('Erreur webcam:', err);
            });

        function captureImage() {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(blob => {
                const file = new File([blob], 'webcam_capture.jpg', { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                webcamInput.files = dataTransfer.files;
                loginWithFace();
            }, 'image/jpeg');
        }
        function loginWithFace() {
    const formData = new FormData();
    const webcamImage = document.getElementById('webcam_image_input').files[0]; // Assure-toi que cet ID correspond à ton input
    formData.append('webcam_image', webcamImage);

    fetch('face_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('face-login-message'); // Ajoute un div pour afficher les messages
        if (data.status === 'success') {
            messageDiv.style.color = 'green';
            messageDiv.textContent = data.message;
            setTimeout(() => {
                window.location.href = 'dashboard.php'; // Redirige vers le tableau de bord
            }, 1000);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('face-login-message').textContent = 'Une erreur s\'est produite. Veuillez réessayer.';
    });
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
            const password = document.getElementById("mot_de_passe").value;
            const passwordError = document.getElementById("password-error");

            if (password.length === 0) {
                passwordError.textContent = "Password is required.";
                passwordError.style.display = "block";
            } else {
                passwordError.style.display = "none";
            }
        }

        function validateForm() {
            const email = document.getElementById("email").value;
            const password = document.getElementById("mot_de_passe").value;
            const emailError = document.getElementById("email-error");
            const passwordError = document.getElementById("password-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;

            // Validate Email
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            // Validate Password
            if (password.length === 0) {
                passwordError.textContent = "Password is required.";
                passwordError.style.display = "block";
                isValid = false;
            } else {
                passwordError.style.display = "none";
            }

            return isValid;
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

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }

        window.onload = function() {
            document.getElementById('email').value = '';
            document.getElementById('mot_de_passe').value = '';
        };
    </script>
</body>
</html>