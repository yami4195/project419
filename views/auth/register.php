<?php
require_once "../../config/database.php";
require_once "../../controllers/AuthController.php";
require_once "../../models/Department.php";
session_start();

$message = "";
$is_success = false;

if (isset($_POST['register'])) {
    $authController = new AuthController($conn);
    $result = $authController->register($_POST);

    $message = $result['message'];
    $is_success = $result['success'];
}

// Fetch departments for the dropdown
$deptModel = new Department($conn);
$departments = $deptModel->getAllDepartments();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Smart Learning System</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .department-container {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translateY(-10px);
        }

        .department-container.visible {
            max-height: 100px;
            opacity: 1;
            transform: translateY(0);
            margin-bottom: 20px;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            font-size: 0.95rem;
            color: #1e293b;
            transition: border-color 0.2s;
        }

        .form-select:focus {
            border-color: #4f46e5;
        }
    </style>
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
                    <select name="role" id="role" class="form-select">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div id="departmentField" class="department-container visible">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-login">Create Account</button>
            </form>

            <div class="register-link">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>

    <script src="../../public/js/auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const departmentField = document.getElementById('departmentField');
            const departmentSelect = document.getElementById('department_id');

            function toggleDepartment() {
                if (roleSelect.value === 'student') {
                    departmentField.classList.add('visible');
                    departmentSelect.setAttribute('required', 'required');
                } else {
                    departmentField.classList.remove('visible');
                    departmentSelect.removeAttribute('required');
                    departmentSelect.value = "";
                }
            }

            roleSelect.addEventListener('change', toggleDepartment);

            // Run on load in case student is pre-selected
            toggleDepartment();
        });
    </script>

</body>

</html>