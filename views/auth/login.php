<?php
require_once "../../config/database.php";
session_start();

$error = "";

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/select_department.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "We couldn't find an account with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Smart Learning System</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-badge">
                <div class="brand-icon">S</div>
                <div class="brand-text">
                    <span class="brand-title">SLEMS</span>
                    <span class="brand-subtitle">Smart Learning &amp; Exam Management</span>
                </div>
            </div>

            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to continue your learning journey.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="name@company.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" id="togglePassword">Show</button>
                    </div>
                </div>

                <div class="options-row">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">Sign In</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Create one</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });

        // Basic form validation feedback
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields.');
            }
        });
    </script>

</body>

</html>