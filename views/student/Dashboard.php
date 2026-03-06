<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../config/database.php";

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 1. Fetch Summary Statistics
$exams_taken = $conn->query("SELECT COUNT(*) as total FROM results WHERE user_id = $user_id")->fetch_assoc()['total'];
$avg_score = $conn->query("SELECT AVG((score/total)*100) as avg FROM results WHERE user_id = $user_id")->fetch_assoc()['avg'] ?? 0;
$best_score = $conn->query("SELECT MAX((score/total)*100) as top FROM results WHERE user_id = $user_id")->fetch_assoc()['top'] ?? 0;
$available_exams = $conn->query("SELECT COUNT(*) as total FROM subjects")->fetch_assoc()['total'];

// 2. Fetch Recent Activity (Last 5 results)
$recent_activity = $conn->query("
    SELECT r.*, s.name as subject_name 
    FROM results r 
    JOIN subjects s ON r.subject_id = s.id 
    WHERE r.user_id = $user_id 
    ORDER BY r.created_at DESC LIMIT 5
");

// 3. Fetch Performance Data for Chart (Performance over time)
$chart_query = $conn->query("
    SELECT (score/total)*100 as percentage, created_at 
    FROM results 
    WHERE user_id = $user_id 
    ORDER BY created_at ASC LIMIT 10
");

$chart_labels = [];
$chart_data = [];
while ($row = $chart_query->fetch_assoc()) {
    $chart_labels[] = date('M d', strtotime($row['created_at']));
    $chart_data[] = round($row['percentage'], 1);
}

// 4. Fetch Subjects for Take Exam section
$subjects = $conn->query("SELECT * FROM subjects");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Smart Learning System</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>SLS</span>
                </div>
                <button id="sidebar-close" class="mobile-only"><i class="fas fa-times"></i></button>
            </div>

            <nav class="sidebar-nav">
                <a href="Dashboard.php" class="nav-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="javascript:void(0)" id="takeExamBtn" class="nav-item">
                    <i class="fas fa-edit"></i>
                    <span>Take Exam</span>
                </a>
                <a href="results.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>My Results</span>
                </a>
                <a href="study_plan.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Study Plan</span>
                </a>
                <a href="performance.php" class="nav-item">
                    <i class="fas fa-analytics"></i>
                    <span>Performance</span>
                </a>
                <div class="nav-divider"></div>
                <a href="../auth/logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-bar">
                <div class="header-left">
                    <button id="sidebar-toggle" class="mobile-only"><i class="fas fa-bars"></i></button>
                    <h1>Smart Learning System</h1>
                </div>

                <div class="header-right">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>

            </header>

            <div class="content-body">
                <!-- Welcome Banner -->
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h2>Welcome Back, <?php echo explode(' ', $user_name)[0]; ?>! 👋</h2>
                        <p>Track your progress and continue your learning journey today.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon indigo">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-value"><?php echo $exams_taken; ?></span>
                            <span class="stat-label">Total Exams</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon emerald">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-value"><?php echo round($avg_score, 1); ?>%</span>
                            <span class="stat-label">Average Score</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon amber">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-value"><?php echo round($best_score, 1); ?>%</span>
                            <span class="stat-label">Best Score</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon rose">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-value"><?php echo $available_exams; ?></span>
                            <span class="stat-label">Available Exams</span>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content Sections -->
                <div class="dashboard-grid">
                    <!-- Performance Chart -->
                    <div class="card chart-section">
                        <div class="card-header">
                            <h3>Performance Trend</h3>
                            <span class="subtitle">Your score across last 10 quizzes</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card activity-section">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                            <a href="results.php" class="view-all">View All</a>
                        </div>
                        <div class="activity-list">
                            <?php if ($recent_activity->num_rows > 0): ?>
                                <?php while ($row = $recent_activity->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <span
                                                class="subject-name"><?php echo htmlspecialchars($row['subject_name']); ?></span>
                                            <span
                                                class="activity-date"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                                        </div>
                                        <div
                                            class="activity-score <?php echo $row['score'] / $row['total'] >= 0.5 ? 'pass' : 'fail'; ?>">
                                            <?php echo $row['score']; ?>/<?php echo $row['total']; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-state">No exams taken yet. Start with a quiz!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Exam Selection -->
    <div class="modal-overlay" id="examModal">
        <div class="modal-content card">
            <div class="modal-header card-header">
                <div>
                    <h3>Available Quizzes</h3>
                    <p class="subtitle">Select a subject to test your knowledge</p>
                </div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="exams-grid">
                <?php if ($subjects->num_rows > 0): ?>
                    <?php
                    $subjects->data_seek(0); // Reset pointer
                    while ($sub = $subjects->fetch_assoc()):
                        ?>
                        <a href="Take_exam.php?subject_id=<?php echo $sub['id']; ?>" class="exam-card">
                            <div class="exam-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <span class="exam-name"><?php echo htmlspecialchars($sub['name']); ?></span>
                            <span class="exam-action">Take Quiz <i class="fas fa-arrow-right"></i></span>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
                <a href="Take_exam.php" class="exam-card general">
                    <div class="exam-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <span class="exam-name">General Quiz</span>
                    <span class="exam-action">Take Mixed Quiz <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Data for Chart.js
        const chartData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Score Percentage',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        };
    </script>
    <script src="../../public/js/dashboard.js"></script>
</body>

</html>