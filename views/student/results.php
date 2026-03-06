<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../config/database.php";
require_once "../../models/Result.php";

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$resultModel = new Result($conn);

// 1. Fetch Summary Stats
$stats = $resultModel->getGeneralStats($user_id);
$total_exams = $stats['total_exams'] ?? 0;
$avg_score = $stats['average_score'] ?? 0;
$best_score = $stats['best_score'] ?? 0;

// 2. Fetch Detailed Results for Table
$results_query = $conn->query("
    SELECT r.*, s.name as subject_name 
    FROM results r 
    JOIN subjects s ON r.subject_id = s.id 
    WHERE r.user_id = $user_id 
    ORDER BY r.created_at DESC
");

// 3. Calculate Pass Rate
$pass_count = 0;
$results_data = [];
if ($results_query->num_rows > 0) {
    while ($row = $results_query->fetch_assoc()) {
        $results_data[] = $row;
        if (($row['score'] / $row['total']) * 100 >= 50) {
            $pass_count++;
        }
    }
}
$pass_rate = ($total_exams > 0) ? ($pass_count / $total_exams) * 100 : 0;

// 4. Chart Data (Last 10 results)
$chart_labels = [];
$chart_scores = [];
$reversed_results = array_reverse(array_slice($results_data, 0, 10));
foreach ($reversed_results as $res) {
    $chart_labels[] = date('M d', strtotime($res['created_at']));
    $chart_scores[] = round(($res['score'] / $res['total']) * 100, 1);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results | Smart Learning System</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/results.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (Same as Dashboard) -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>SLS</span>
                </div>
                <button id="sidebar-close" class="mobile-only"><i class="fas fa-times"></i></button>
            </div>
            
            <nav class="sidebar-nav">
                <a href="Dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="javascript:void(0)" id="takeExamBtn" class="nav-item">
                    <i class="fas fa-edit"></i>
                    <span>Take Exam</span>
                </a>
                <a href="results.php" class="nav-item active">
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
            <header class="top-bar">
                <div class="header-left">
                    <button id="sidebar-toggle" class="mobile-only"><i class="fas fa-bars"></i></button>
                    <h1>My Exam Results</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                        <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <!-- Hero Analytics Section -->
                <div class="hero-analytics">
                    <div class="analytics-left">
                        <div class="card welcome-card">
                            <h2>Academic Performance Hub</h2>
                            <p>Detailed breakdown of your quiz attempts, score trends, and success rates.</p>
                        </div>
                        
                        <div class="stats-mini-grid">
                            <div class="stat-card glass">
                                <div class="stat-icon indigo"><i class="fas fa-clipboard-check"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $total_exams; ?></span>
                                    <span class="stat-label">Total Exams</span>
                                </div>
                            </div>
                            <div class="stat-card glass">
                                <div class="stat-icon emerald"><i class="fas fa-star"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo round($avg_score, 1); ?>%</span>
                                    <span class="stat-label">Average Score</span>
                                </div>
                            </div>
                            <div class="stat-card glass">
                                <div class="stat-icon amber"><i class="fas fa-award"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo round($best_score, 1); ?>%</span>
                                    <span class="stat-label">Best Score</span>
                                </div>
                            </div>
                            <div class="stat-card glass">
                                <div class="stat-icon rose"><i class="fas fa-bullseye"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo round($pass_rate, 1); ?>%</span>
                                    <span class="stat-label">Pass Rate</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="analytics-right">
                        <div class="card chart-hero glass">
                            <div class="card-header">
                                <h3>Performance Trend</h3>
                                <p class="subtitle">Percentage scores over time</p>
                            </div>
                            <div class="chart-container-hero">
                                <canvas id="resultsTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historical Table Section (Separate) -->
                <div class="results-table-container">
                    <div class="card table-card-wide glass">
                        <div class="card-header table-header">
                            <div>
                                <h3>Attempt History</h3>
                                <p class="subtitle">Complete record of your acadmic performance</p>
                            </div>
                            <div class="table-actions">
                                <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($results_data)): ?>
                                        <?php foreach ($results_data as $row): 
                                            $pct = ($row['score'] / $row['total']) * 100;
                                        ?>
                                            <tr>
                                                <td class="bold"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                                <td><span class="score-text"><?php echo $row['score']; ?> / <?php echo $row['total']; ?></span></td>
                                                <td>
                                                    <div class="progress-wrap">
                                                        <span class="pct-val"><?php echo round($pct, 1); ?>%</span>
                                                        <div class="progress-bar-mini">
                                                            <div class="progress-fill" style="width: <?php echo $pct; ?>%; background: <?php echo $pct >= 50 ? 'var(--emerald)' : 'var(--rose)'; ?>"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge-chip <?php echo $pct >= 50 ? 'pass' : 'fail'; ?>">
                                                        <i class="fas <?php echo $pct >= 50 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                        <?php echo $pct >= 50 ? 'Pass' : 'Fail'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="empty-text">No results found yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
                <?php 
                // We need to ensure $results_query or a separate subjects query is ready
                // Let's refetch subjects for the modal
                $all_subjects = $conn->query("SELECT * FROM subjects");
                if ($all_subjects->num_rows > 0): 
                    while($sub = $all_subjects->fetch_assoc()): 
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
        const trendData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            scores: <?php echo json_encode($chart_scores); ?>
        };
    </script>
    <script src="../../public/js/dashboard.js"></script>
    <script src="../../public/js/results.js"></script>
</body>
</html>