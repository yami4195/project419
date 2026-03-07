<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";
require_once "check_dept.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Stats helper
function getCount($conn, $table, $condition = "")
{
    $sql = "SELECT COUNT(*) as total FROM $table $condition";
    $res = $conn->query($sql);
    return $res ? $res->fetch_assoc()['total'] : 0;
}

$dept_id = $_SESSION['admin_dept_id'];

$total_students = getCount($conn, "users", "WHERE role = 'student' AND department_id = $dept_id");
$total_subjects = getCount($conn, "subjects", "WHERE department_id = $dept_id");
$total_exams = getCount($conn, "results r JOIN users u ON r.user_id = u.id", "WHERE r.attempt = 1 AND u.department_id = $dept_id");
$total_questions = getCount($conn, "questions", "WHERE department_id = $dept_id");

// Fetch Recent Activity (Filtered by Dept)
$recent_results = $conn->query("SELECT r.*, u.name as student_name, s.name as subject_name 
                                FROM results r 
                                JOIN users u ON r.user_id = u.id 
                                JOIN subjects s ON r.subject_id = s.id 
                                WHERE u.department_id = $dept_id
                                ORDER BY r.created_at DESC LIMIT 5");

// Chart Data: Performance per Subject (Filtered by Dept)
$perf_query = $conn->query("SELECT s.name, AVG((r.score/r.total)*100) as avg_score 
                            FROM results r 
                            JOIN subjects s ON r.subject_id = s.id 
                            WHERE s.department_id = $dept_id
                            GROUP BY s.id LIMIT 6");
$perf_labels = [];
$perf_scores = [];
while ($row = $perf_query->fetch_assoc()) {
    $perf_labels[] = $row['name'];
    $perf_scores[] = round($row['avg_score'], 1);
}

// Chart Data: Subject Participation (Filtered by Dept)
$part_query = $conn->query("SELECT s.name, COUNT(r.id) as count 
                            FROM results r 
                            JOIN subjects s ON r.subject_id = s.id 
                            WHERE s.department_id = $dept_id
                            GROUP BY s.id LIMIT 5");
$part_labels = [];
$part_counts = [];
while ($row = $part_query->fetch_assoc()) {
    $part_labels[] = $row['name'];
    $part_counts[] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Smart Learning System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="system-name">Smart Learning</span>
            </div>

            <!-- Department Context Display -->
            <div
                style="margin: 0 16px 16px; padding: 16px; background: rgba(255,255,255,0.05); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <p
                    style="color: var(--sidebar-text); font-size: 0.7rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                    Current Dept</p>
                <p style="color: white; font-weight: 600; font-size: 0.85rem;">
                    <?php echo $_SESSION['admin_dept_name']; ?>
                </p>
                <a href="select_department.php"
                    style="color: var(--primary); font-size: 0.75rem; text-decoration: none; font-weight: 600; display: block; margin-top: 8px;">
                    <i class="fas fa-exchange-alt"></i> Switch Department
                </a>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-label">Main Menu</p>
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_subject.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    <span>Manage Subjects</span>
                </a>
                <a href="set_timer.php" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>Set Timer</span>
                </a>
                <a href="manage_questions.php" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Manage Questions</span>
                </a>

                <p class="nav-label">Reporting</p>
                <a href="all_results.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>View Results</span>
                </a>
                <a href="manage_students.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Manage Students</span>
                </a>

                <a href="../auth/logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-nav">
                <div class="top-nav-right">
                    <div class="nav-icons">

                    </div>

                    <div class="admin-profile">
                        <div class="admin-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <div class="admin-info">
                            <span class="admin-name">
                                <?php echo $_SESSION['user_name']; ?>
                            </span>
                            <span class="admin-role"><?php echo $_SESSION['admin_dept_name']; ?> Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <div class="page-header">
                    <h2>Admin Overview</h2>
                    <p>Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>. Here's what's happening
                        today.</p>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <h3>Total Students</h3>
                            <div class="stat-number"><?php echo $total_students; ?></div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>12% from last month</span>
                            </div>
                        </div>
                        <div class="stat-icon-box indigo">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-content">
                            <h3>Active Subjects</h3>
                            <div class="stat-number"><?php echo $total_subjects; ?></div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>2 new today</span>
                            </div>
                        </div>
                        <div class="stat-icon-box emerald">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-content">
                            <h3>Exams Taken</h3>
                            <div class="stat-number"><?php echo $total_exams; ?></div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>24% increase</span>
                            </div>
                        </div>
                        <div class="stat-icon-box amber">
                            <i class="fas fa-vial"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-content">
                            <h3>Question Bank</h3>
                            <div class="stat-number"><?php echo $total_questions; ?></div>
                            <div class="stat-trend up">
                                <i class="fas fa-plus"></i>
                                <span>5 added recently</span>
                            </div>
                        </div>
                        <div class="stat-icon-box rose">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts & Activity Grid -->
                <div class="dashboard-main-grid">
                    <!-- Performance Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Average Performance per Subject</h3>
                        </div>
                        <div style="height: 350px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Quiz Activity</h3>
                            <a href="all_results.php" class="view-all"
                                style="font-size: 0.8rem; font-weight: 600; color: var(--primary); text-decoration: none;">View
                                All</a>
                        </div>
                        <div class="activity-list">
                            <?php if ($recent_results && $recent_results->num_rows > 0): ?>
                                <?php while ($act = $recent_results->fetch_assoc()):
                                    $pct = ($act['score'] / $act['total']) * 100;
                                    $status_class = ($pct >= 50) ? 'badge-success' : 'badge-danger';
                                    $status_text = ($pct >= 50) ? 'Passed' : 'Failed';
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-avatar indigo">
                                            <?php echo strtoupper(substr($act['student_name'], 0, 1)); ?>
                                        </div>
                                        <div class="activity-info">
                                            <div class="activity-title"><?php echo $act['student_name']; ?>
                                                <span>completed</span>
                                            </div>
                                            <div class="activity-time"><?php echo $act['subject_name']; ?> •
                                                <?php echo date('M d, H:i', strtotime($act['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-muted); padding: 20px;">No recent activity
                                    found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const performanceData = {
            labels: <?php echo json_encode($perf_labels); ?>,
            scores: <?php echo json_encode($perf_scores); ?>
        };
        const participationData = {
            labels: <?php echo json_encode($part_labels); ?>,
            counts: <?php echo json_encode($part_counts); ?>
        };
    </script>
    <script src="../../public/js/admin_dashboard.js"></script>
</body>

</html>