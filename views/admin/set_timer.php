<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";
require_once "check_dept.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

// Handle Update Exam Settings (Time Limit)
if (isset($_POST['update_exam'])) {
    $sid = $_POST['subject_id'];
    $limit = $_POST['time_limit'];
    if ($conn->query("UPDATE subjects SET time_limit = $limit WHERE id = $sid")) {
        $message = "Exam settings updated successfully!";
    } else {
        $message = "Error updating settings: " . $conn->error;
    }
}

// Fetch subjects (filtered by department)
$dept_id = $_SESSION['admin_dept_id'];
$subjects_result = $conn->query("SELECT s.*, (SELECT COUNT(*) FROM questions q WHERE q.subject_id = s.id) as question_count FROM subjects s WHERE s.department_id = $dept_id ORDER BY s.name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Timer | Smart Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
    <style>
        .exam-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .exam-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .exam-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .exam-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .q-count {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn-save {
            width: 100%;
            padding: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-weight: 600;
            background: var(--emerald-light);
            color: var(--emerald);
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
                <a href="set_timer.php" class="nav-item active">
                    <i class="fas fa-clock"></i><span>Set Timer</span>
                </a>
                <a href="manage_questions.php" class="nav-item">
                    <i class="fas fa-question-circle"></i><span>Manage Questions</span>
                </a>
                <p class="nav-label">Reporting</p>
                <a href="all_results.php" class="nav-item">
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
                    <h2>Exam Timer Configuration</h2>
                    <p>Manage time limits for <strong><?php echo $_SESSION['admin_dept_name']; ?></strong> exams.</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="exam-grid">
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <div class="exam-card">
                            <div class="exam-header">
                                <span class="exam-title">
                                    <?php echo $row['name']; ?>
                                </span>
                                <span class="q-count">
                                    <?php echo $row['question_count']; ?> Qs
                                </span>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Time Limit (Minutes)</label>
                                    <input type="number" name="time_limit" value="<?php echo $row['time_limit']; ?>"
                                        class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <div
                                        style="font-size: 0.85rem; font-weight: 700; color: <?php echo $row['question_count'] > 0 ? 'var(--emerald)' : 'var(--rose)'; ?>;">
                                        <?php echo $row['question_count'] > 0 ? 'Ready for Exam' : 'Needs Questions'; ?>
                                    </div>
                                </div>
                                <button type="submit" name="update_exam" class="btn-save">Update Settings</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="../../public/js/admin_dashboard.js"></script>
</body>

</html>