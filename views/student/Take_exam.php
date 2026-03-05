<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";
require_once "../../controllers/QuizController.php";

if (!$conn) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quizController = new QuizController($conn);

$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : (isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0);

// Check Eligibility
$eligibility = $quizController->checkEligibility($user_id, $subject_id);

if (isset($_POST['submit_exam'])) {
    if (!$eligibility['eligible']) {
        die($eligibility['message']);
    }

    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
    $result = $quizController->submitQuiz($user_id, $subject_id, $answers);

    if ($result['success']) {
        echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: 50px auto; text-align: center; font-family: Arial, sans-serif;'>";
        echo "<h2 style='color: #4CAF50;'>Quiz Completed!</h2>";
        echo "<h3>Your Score: " . $result['score'] . " / " . $result['total'] . "</h3>";

        $percentage = ($result['total'] > 0) ? ($result['score'] / $result['total']) * 100 : 0;
        if ($percentage >= 50) {
            echo "<p style='color: #4CAF50; font-weight: bold;'>Congratulations! You passed!</p>";
        } else {
            echo "<p style='color: #f44336; font-weight: bold;'>You did not pass this time.</p>";
        }

        echo "<br><br><a href='Dashboard.php' style='background: #333; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Back to Dashboard</a>";
        echo "</div>";
    } else {
        echo "Error: " . $result['message'];
    }
    exit();
}

if (!$eligibility['eligible']) {
    echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: 50px auto; text-align: center; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #f44336;'>Access Denied</h2>";
    echo "<p>{$eligibility['message']}</p>";
    echo "<br><br><a href='Dashboard.php' style='background: #333; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Back to Dashboard</a>";
    echo "</div>";
    exit();
}

$subject_name = "General Quiz";
$time_limit = 0;

if ($subject_id > 0) {
    $sub_res = $conn->query("SELECT name, time_limit FROM subjects WHERE id = $subject_id");
    if ($sub_res && $sub_row = $sub_res->fetch_assoc()) {
        $subject_name = $sub_row['name'];
        $time_limit = $sub_row['time_limit'];
    }
}
?>


<h2>Take Quiz: <?php echo htmlspecialchars($subject_name); ?></h2>

<?php if ($time_limit > 0): ?>
    <div id="timer" style="font-size: 20px; font-weight: bold; color: red; margin-bottom: 20px;">
        Time Remaining: <span id="time">--:--</span>
    </div>
    <script>
        var timeLeft = <?php echo $time_limit * 60; ?>;
        var timerDisplay = document.getElementById('time');

        function startTimer() {
            var timer = setInterval(function () {
                var minutes = Math.floor(timeLeft / 60);
                var seconds = timeLeft % 60;

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                timerDisplay.textContent = minutes + ":" + seconds;

                if (--timeLeft < 0) {
                    clearInterval(timer);
                    alert("Time is up! Your quiz will be submitted automatically.");
                    document.getElementById('quizForm').submit();
                }
            }, 1000);
        }
        window.onload = startTimer;
    </script>
<?php endif; ?>

<form method="POST" id="quizForm">
    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">

    <?php
    $sql = $subject_id > 0 ? "SELECT * FROM questions WHERE subject_id = $subject_id" : "SELECT * FROM questions";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        echo "No questions found for this subject!";
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