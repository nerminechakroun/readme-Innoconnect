<?php
require_once __DIR__ . '/../../config.php';
session_start();

$conn = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $code = $_POST['code'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ? AND reset_code = ? AND reset_expiry > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ?, reset_code = NULL, reset_expiry = NULL WHERE id_utilisateur = ?");
        $stmt->bind_param("si", $hashed_password, $user['id_utilisateur']);
        $stmt->execute();
        $stmt->close();
        $success = "Your password has been reset successfully. You can now <a href='login.php'>log in</a>.";
    } else {
        $error = "Invalid, expired, or incorrect reset code or email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
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
                    <li style="margin: 0 15px;"><a href="register.php" style="color: white; text-decoration: none; font-weight: 500;">Sign Up</a></li>
                    <li style="margin: 0 15px;"><a href="login.php" style="color: white; text-decoration: none; font-weight: 500;">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <button class="theme-toggle" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>

    <div class="register-container">
        <h2>Reset Password</h2>
        <p class="slogan">Enter your email, reset code, and new password.</p>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php else: ?>
            <form method="POST" onsubmit="showLoader()" autocomplete="off">
                <input type="text" style="display:none" name="fake-username">
                <input type="password" style="display:none" name="fake-password">
                <div class="form-group input-with-icon">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Email"  oninput="validateEmail()" autocomplete="off">
                    <span id="email-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="code">Reset Code</label>
                    <i class="fas fa-key"></i>
                    <input type="text" id="code" name="code" placeholder="Reset Code"  autocomplete="off">
                </div>
                <div class="form-group input-with-icon">
                    <label for="new_password">New Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="new_password" name="new_password" placeholder="New Password" autocomplete="off">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password')"></i>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" class="register-btn btn-primary">Reset</button>
                    <a href="login.php" class="btn-danger">Back</a>
                </div>
            </form>
        <?php endif; ?>
        <p>Back to <a href="login.php">login</a></p>
    </div>

    <footer>
        <p>© 2025 InnoConnect. All rights reserved.</p>
    </footer>

    <script>
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

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle i');
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                document.querySelector('.theme-toggle i').classList.remove('fa-moon');
                document.querySelector('.theme-toggle i').classList.add('fa-sun');
            }
        });

        document.querySelector('.theme-toggle').addEventListener('click', toggleDarkMode);

        window.onload = function() {
            document.getElementById('email').value = '';
            document.getElementById('code').value = '';
            document.getElementById('new_password').value = '';
        };
    </script>

    <style>
        /* Adjust main content to account for fixed header */
        .register-container {
            margin-top: 100px;
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
</body>
</html>