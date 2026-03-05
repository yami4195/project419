<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM results WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Quiz Results</h2>

<table border="1" cellpadding="10" style="border-collapse:collapse">
    <tr>
        <th>Score</th>
        <th>Total</th>
        <th>Percentage</th>
        <th>Status</th>
        <th>Date</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $percentage = ($row['score'] / $row['total']) * 100;

            if ($percentage >= 50) {
                $status = "Pass";
            } else {
                $status = "Fail";
            }

            echo "<tr>
                <td>{$row['score']}</td>
                <td>{$row['total']}</td>
                <td>" . round($percentage, 2) . "%</td>
                <td>$status</td>
                <td>{$row['created_at']}</td>
              </tr>";
        }

    } else {

        echo "<tr><td colspan='5'>No results yet</td></tr>";

    }
    ?>

</table>


<br>
<a href="dashboard.php">Back to Dashboard</a>