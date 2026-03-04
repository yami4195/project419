<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";

if (!$conn) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<h2>Take Quiz</h2>

<form method="POST">

    <?php
    $result = $conn->query("SELECT * FROM questions");
    if ($result->num_rows == 0) {
        echo "No question found!";
        exit();
    }
    while ($question = $result->fetch_assoc()) {

        echo "<p><strong>{$question['question_text']}</strong></p>";

        $qid = $question['id'];
        $options = $conn->query("SELECT * FROM options WHERE question_id = $qid");

        while ($option = $options->fetch_assoc()) {
            echo "<input type='radio' name='answers[$qid]' value='{$option['id']}' required>
              {$option['option_text']}<br>";
        }

        echo "<br>";
    }
    ?>

    <button type="submit" name="submit_exam">Submit</button>

</form>