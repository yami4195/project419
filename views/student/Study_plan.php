<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../config/database.php";
require_once "../../controllers/StudyPlannerController.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$planner = new StudyPlannerController($conn);

if (isset($_POST['generate_plan'])) {
    $planner->generatePlan($user_id);
    header("Location: study_plan.php");
    exit();
}

$plan = $planner->getMyStudyPlan($user_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Study Plan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #45a049; }
        .header { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Personalized Study Plan</h2>
        <form method="POST">
            <button type="submit" name="generate_plan" class="btn">Refresh My Plan</button>
        </form>
    </div>

    <p>Based on your previous performance, we recommend focusing on the following subjects:</p>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Recommended Hours</th>
                <th>Scheduled Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($plan)): ?>
                <?php foreach ($plan as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo $row['recommended_hours']; ?> hours</td>
                        <td><?php echo date('M d, Y', strtotime($row['study_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center;">No study plan generated yet. Click "Refresh" to start!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="Dashboard.php" class="btn" style="background-color: #333;">Back to Dashboard</a>
</div>

</body>
</html>
