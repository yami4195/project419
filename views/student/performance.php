<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../config/database.php";
require_once "../../controllers/QuizController.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quizController = new QuizController($conn);
$data = $quizController->getPerformanceData($user_id);

$stats = $data['stats'];
$subjectPerformance = $data['subject_performance'];

// Prepare data for Chart.js
$chartLabels = [];
$chartData = [];
$weakSubjects = [];

foreach ($subjectPerformance as $row) {
    $chartLabels[] = $row['subject_name'];
    $chartData[] = round($row['average_percentage'], 2);

    if ($row['average_percentage'] < 50) {
        $weakSubjects[] = $row['subject_name'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Performance Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .card .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .alert {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            border-left: 5px solid #ffeeba;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Performance Analytics</h2>

        <div class="stats-grid">
            <div class="card">
                <h3>Total Exams Taken</h3>
                <div class="value">
                    <?php echo $stats['total_exams'] ?? 0; ?>
                </div>
            </div>
            <div class="card">
                <h3>Average Score</h3>
                <div class="value">
                    <?php echo round($stats['average_score'] ?? 0, 1); ?>%
                </div>
            </div>
            <div class="card">
                <h3>Best Score</h3>
                <div class="value">
                    <?php echo round($stats['best_score'] ?? 0, 1); ?>%
                </div>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>

        <?php if (!empty($weakSubjects)): ?>
            <div class="alert">
                <strong>⚠️ Weak Subjects Detected:</strong>
                <?php echo implode(", ", $weakSubjects); ?>.
                We recommend visiting your <a href="study_plan.php">Study Plan</a> to improve these areas.
            </div>
        <?php else: ?>
            <div class="card" style="background: #d4edda; color: #155724; border: none;">
                <strong>🎉 Great Job!</strong> You are performing well across all subjects.
            </div>
        <?php endif; ?>

        <br>
        <a href="Dashboard.php" class="btn">Back to Dashboard</a>
    </div>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Average Score (%)',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: 'rgba(76, 175, 80, 0.6)',
                borderColor: 'rgba(76, 175, 80, 1)',
                borderWidth: 1
        }]
    },
            options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Subject-wise Performance Comparison'
                }
            }
        }
});
    </script>

</body>

</html>