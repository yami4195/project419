<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<h2>Student Dashboard</h2>
<p>Welcome <?php echo $_SESSION['user_name']; ?></p>

<div style="background: #eef; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>Smart Features:</strong>
    <a href="study_plan.php" style="font-weight: bold; color: #4CAF50;">AI Study Plan</a> |
    <a href="performance.php" style="font-weight: bold; color: #2196F3;">Performance Analytics</a> |
    <a href="leaderboard.php" style="font-weight: bold; color: #FF9800;">Leaderboard</a>
</div>

<h3>Take a Quiz</h3>
<ul>
    <?php
    require_once "../../config/database.php";
    $subjects = $conn->query("SELECT * FROM subjects");
    if ($subjects->num_rows > 0) {
        while ($sub = $subjects->fetch_assoc()) {
            echo "<li><a href='Take_exam.php?subject_id={$sub['id']}'>Take {$sub['name']} Quiz</a></li>";
        }
    } else {
        echo "<li>No subjects available yet.</li>";
    }
    ?>
    <li><a href="Take_exam.php">Take General Quiz (All Subjects)</a></li>
</ul>

<hr>
<a href="results.php">View My Results</a><br><br>
<a href="../auth/logout.php"><button>Logout</button></a>