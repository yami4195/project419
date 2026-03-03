<?php
require_once "../../config/database.php";

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name', '$email', '$hashed_password', '$role')";

    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
</head>

<body>

    <h2>Register</h2>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <label for="role">Select Role</label>
        <select name="role" required>

            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit" name="register">Register</button>
    </form>

</body>

</html>