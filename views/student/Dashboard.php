<?php
error_reporting(E_ALL);
ini_set('display_erors', 1);
session_start();

if ($_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<h2>Student Dashboard</h2>
<p>Welcome <?php echo $_SESSION['user_name']; ?></p>
<a href="Take_exam.php">Take Quiz</a><br><br>
<a href="../auth/logout.php">Logout</a>