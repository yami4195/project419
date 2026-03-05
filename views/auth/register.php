<?php
require_once "../../config/database.php";
require_once "../../controllers/AuthController.php";
session_start();

$message = "";
$is_success = false;

if (isset($_POST['register'])) {
    $authController = new AuthController($conn);
    $result = $authController->register($_POST);

    $message = $result['message'];
    $is_success = $result['success'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Smart Learning System</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>

    <div class="login-container" style="max-width: 500px;">
        <div class="login-card">
            <div class="login-header">
                <h1>Create Account</h1>
                <p>Join our learning community today</p>
            </div>

            <?php if ($message): ?>
                <div class="<?php echo $is_success ? 'card' : 'error-message'; ?>"
                    style="<?php echo $is_success ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" placeholder="John Doe" required>
                </div>

                <div class="input-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="johndoe123" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" placeholder="john@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle">Show</button>
                    </div>
                    <div class="strength-meter">
                        <div id="strengthBar" class="strength-bar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••"
                            required>
                        <button type="button" class="password-toggle">Show</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">User Role</label>
                    <select name="role" id="role"
                        style="width: 100%; padding: 12px 16px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; outline: none;">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" name="register" class="btn-login">Create Account</button>
            </form>

            <div class="register-link">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>

    <script src="../../public/js/auth.js"></script>

</body>

</html>