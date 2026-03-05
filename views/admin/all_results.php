<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$adminController = new AdminController($conn);
$results = $adminController->viewResults();
?>

<!DOCTYPE html>
<html>

<head>
    <title>All Student Results</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .pass {
            color: green;
            font-weight: bold;
        }

        .fail {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>All Student Exam Results</h2>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Score</th>
                <th>Total Marks</th>
                <th>Percentage</th>
                <th>Status</th>
                <th>Exam Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <?php
                    $percentage = ($row['total'] > 0) ? ($row['score'] / $row['total']) * 100 : 0;
                    $status = ($percentage >= 50) ? "Pass" : "Fail";
                    $statusClass = ($percentage >= 50) ? "pass" : "fail";
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($row['student_name']); ?>
                        </td>
                        <td>
                            <?php echo $row['score']; ?>
                        </td>
                        <td>
                            <?php echo $row['total']; ?>
                        </td>
                        <td>
                            <?php echo round($percentage, 2); ?>%
                        </td>
                        <td class="<?php echo $statusClass; ?>">
                            <?php echo $status; ?>
                        </td>
                        <td>
                            <?php echo $row['created_at']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No results found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</body>

</html>