<?php
require_once __DIR__ . '/../../config.php';
session_start();

// Générer un jeton CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $email = trim($_POST['email']);

        // Server-side email validation
        if (empty($email)) {
            $error = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Check if the email exists in the database
            $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]); // Pass the parameter directly in execute()
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $code = sprintf("%06d", mt_rand(0, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Update the user with the reset code and expiry
                $stmt = $conn->prepare("UPDATE utilisateur SET reset_code = ?, reset_expiry = ? WHERE id_utilisateur = ?");
                $stmt->execute([$code, $expiry, $user['id_utilisateur']]);

                $success = "Your reset code is: <strong>$code</strong>. Use this code to reset your password on the next page. <a href='reset_password.php'>Reset now</a>";
            } else {
                $error = "No account associated with this email.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
</head>
<body>
    <div class="loader" id="loader"></div>
    <header>
        <div class="logo">
            <img src="../../innoconnect.jpeg" alt="InnoConnect Logo">
        </div>
        <nav>
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <button class="theme-toggle" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>

    <div class="register-container">
        <h2>Forgot Password</h2>
        <p class="slogan">Enter your email to receive a reset code.</p>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="POST" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="text" style="display:none" name="fake-username">
                <input type="password" style="display:none" name="fake-password">
                <div class="form-group input-with-icon">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Email" oninput="validateEmail()" autocomplete="off">
                    <span id="email-error" class="error-message" style="display: none;"></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" class="register-btn btn-primary">Get Code</button>
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

        function validateForm() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("email-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;

            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            return isValid;
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
        };
    </script>
</body>
</html>