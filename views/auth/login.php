<?php
require_once "../../config/database.php";
session_start();

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            echo "Login successful!";
            echo "<br>Welcome " . $_SESSION['user_name'];
            echo "<br>Your role is: " . $_SESSION['user_role'];
        } else {
            echo "Wrong password!";
        }

    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>

    <h2>Login</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>

        <button type="submit" name="login">Login</button>
    </form>

</body>

</html>