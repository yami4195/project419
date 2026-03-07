<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";
require_once "../../models/Result.php";
require_once "check_dept.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all results (filtered by department)
$dept_id = $_SESSION['admin_dept_id'];
$sql = "SELECT r.*, u.name as student_name, s.name as subject_name
        FROM results r
        JOIN users u ON r.user_id = u.id
        JOIN subjects s ON r.subject_id = s.id
        WHERE u.department_id = $dept_id
        ORDER BY r.created_at DESC";
$results = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Results | Smart Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
    <style>
        .results-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .results-table th {
            padding: 12px 20px;
            text-align: left;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .results-table tr {
            background: white;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
        }

        .results-table td {
            padding: 16px 20px;
        }

        .results-table tr td:first-child {
            border-radius: var(--radius) 0 0 var(--radius);
        }

        .results-table tr td:last-child {
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .percent-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .percent-pass {
            background: var(--emerald-light);
            color: var(--emerald);
        }

        .percent-fail {
            background: var(--rose-light);
            color: var(--rose);
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
                <a href="manage_subject.php" class="nav-item">
                    <i class="fas fa-book"></i><span>Manage Subjects</span>
                </a>
                <a href="set_timer.php" class="nav-item">
                    <i class="fas fa-clock"></i><span>Set Timer</span>
                </a>
                <a href="manage_questions.php" class="nav-item">
                    <i class="fas fa-question-circle"></i><span>Manage Questions</span>
                </a>
                <p class="nav-label">Reporting</p>
                <a href="all_results.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i><span>View Results</span>
                </a>
                <a href="manage_students.php" class="nav-item">
                    <i class="fas fa-users"></i><span>Manage Students</span>
                </a>
                <a href="../auth/logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-nav">
                <div class="top-nav-right">
                    <div class="admin-profile">
                        <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
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
            <h2>Exam Results</h2>
            <p>Overview of all student performances across subjects.</p>
        </div>

        <div class="card">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row):
                            $pct = ($row['total'] > 0) ? ($row['score'] / $row['total']) * 100 : 0;
                            $is_pass = $pct >= 50;
                            ?>
                            <tr>
                                <td><strong><?php echo $row['student_name']; ?></strong></td>
                                <td><?php echo $row['subject_name'] ?? 'N/A'; ?></td>
                                <td><?php echo $row['score']; ?> / <?php echo $row['total']; ?></td>
                                <td><?php echo round($pct, 1); ?>%</td>
                                <td>
                                    <span class="percent-badge <?php echo $is_pass ? 'percent-pass' : 'percent-fail'; ?>">
                                        <?php echo $is_pass ? 'Passed' : 'Failed'; ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </main>
    </div>
    <script src="../../public/js/admin_dashboard.js"></script>
</body>

</html>