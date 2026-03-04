<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
if (isset($_POST['add_question'])) {

    $subject_id = $_POST['subject_id'];
    $question_text = $_POST['question_text'];

    $conn->query("INSERT INTO questions (subject_id, question_text)
                  VALUES ('$subject_id', '$question_text')");

    $question_id = $conn->insert_id;

    $options = [
        $_POST['option1'],
        $_POST['option2'],
        $_POST['option3'],
        $_POST['option4']
    ];

    $correct = $_POST['correct_option'];

    for ($i = 0; $i < 4; $i++) {
        $is_correct = ($correct == $i + 1) ? 1 : 0;

        $conn->query("INSERT INTO options (question_id, option_text, is_correct)
                      VALUES ('$question_id', '{$options[$i]}', '$is_correct')");
    }

    echo "Question added successfully!<br>";
}
// Insert Subject
if (isset($_POST['add_subject'])) {
    $name = $_POST['subject_name'];

    $sql = "INSERT INTO subjects (name) VALUES ('$name')";
    if ($conn->query($sql) === TRUE) {
        echo "Subject added successfully! <br>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<h2>Admin Dashboard</h2>
<p>Welcome <?php echo $_SESSION['user_name']; ?></p>

<h3>Add Subject</h3>

<form method="POST">
    <input type="text" name="subject_name" placeholder="Subject Name" required>
    <button type="submit" name="add_subject">Add Subject</button>
</form>

<br>
<h3>All Subjects</h3>

<?php
$result = $conn->query("SELECT * FROM subjects");

while ($row = $result->fetch_assoc()) {
    echo $row['id'] . " - " . $row['name'] . "<br>";
}

?>
<h3>Add Question</h3>

<form method="POST">
    <select name="subject_id" required>
        <option value="">Select Subject</option>
        <?php
        $subjects = $conn->query("SELECT * FROM subjects");
        while ($sub = $subjects->fetch_assoc()) {
            echo "<option value='{$sub['id']}'>{$sub['name']}</option>";
        }
        ?>
    </select><br><br>

    <textarea name="question_text" placeholder="Enter question" required></textarea><br><br>

    <input type="text" name="option1" placeholder="Option 1" required><br>
    <input type="text" name="option2" placeholder="Option 2" required><br>
    <input type="text" name="option3" placeholder="Option 3" required><br>
    <input type="text" name="option4" placeholder="Option 4" required><br><br>

    Correct Option:
    <select name="correct_option" required>
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
        <option value="3">Option 3</option>
        <option value="4">Option 4</option>
    </select><br><br>

    <button type="submit" name="add_question">Add Question</button>
</form>
<a href="../auth/logout.php">Logout</a>