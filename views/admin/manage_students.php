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

// Fetch students (filtered by department)
$dept_id = $_SESSION['admin_dept_id'];
$sql = "SELECT u.id, u.name, u.email,
               (SELECT COUNT(*) FROM results r WHERE r.user_id = u.id) as total_exams,
               (SELECT AVG((score/total)*100) FROM results r WHERE r.user_id = u.id) as avg_score
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.role = 'student' AND u.department_id = $dept_id
        ORDER BY u.id DESC";
$students = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Smart Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
    <style>
        .student-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .student-table th {
            padding: 12px 20px;
            text-align: left;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .student-table tr {
            background: white;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
        }

        .student-table td {
            padding: 20px;
        }

        .student-table tr td:first-child {
            border-radius: var(--radius) 0 0 var(--radius);
        }

        .student-table tr td:last-child {
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .avatar-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .score-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .score-high {
            background: var(--emerald-light);
            color: var(--emerald);
        }

        .score-mid {
            background: var(--amber-light);
            color: var(--amber);
        }

        .score-low {
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
                    <?php echo $_SESSION['admin_dept_name']; ?></p>
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
                <a href="all_results.php" class="nav-item">
                    <i class="fas fa-chart-line"></i><span>View Results</span>
                </a>
                <a href="manage_students.php" class="nav-item active">
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
                    <h2>Manage Students</h2>
                    <p>View and manage students in <strong><?php echo $_SESSION['admin_dept_name']; ?></strong>.</p>
                </div>

                <div class="card">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Exams Taken</th>
                                <th>Avg. Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $students->fetch_assoc()):
                                $avg_val = $row['avg_score'] ?? 0;
                                $avg = round((float) $avg_val, 1);
                                $score_class = $avg >= 80 ? 'score-high' : ($avg >= 50 ? 'score-mid' : 'score-low');
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="avatar-circle">
                                                <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                            </div>
                                            <span style="font-weight: 600;">
                                                <?php echo $row['name']; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $row['email']; ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo $row['total_exams']; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['total_exams'] > 0): ?>
                                            <span class="score-badge <?php echo $score_class; ?>">
                                                <?php echo $avg; ?>%
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../../public/js/admin_dashboard.js"></script>
</body>

</html>