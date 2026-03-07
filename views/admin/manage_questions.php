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

$message = "";

// Handle Add Question
if (isset($_POST['add_question'])) {
    $subject_id = $_POST['subject_id'];
    $question_text = $conn->real_escape_string($_POST['question_text']);

    $dept_id = $_SESSION['admin_dept_id'];

    if ($conn->query("INSERT INTO questions (subject_id, question_text, department_id) VALUES ('$subject_id', '$question_text', '$dept_id')")) {
        $question_id = $conn->insert_id;
        $options = [
            $conn->real_escape_string($_POST['option1']),
            $conn->real_escape_string($_POST['option2']),
            $conn->real_escape_string($_POST['option3']),
            $conn->real_escape_string($_POST['option4'])
        ];
        $correct = $_POST['correct_option'];

        for ($i = 0; $i < 4; $i++) {
            $is_correct = ($correct == $i + 1) ? 1 : 0;
            $conn->query("INSERT INTO options (question_id, option_text, is_correct) 
                          VALUES ('$question_id', '{$options[$i]}', '$is_correct')");
        }
        $message = "Question added successfully!";
    } else {
        $message = "Error adding question: " . $conn->error;
    }
}

// Fetch subjects for dropdown (filtered by department)
$dept_id = $_SESSION['admin_dept_id'];
$subjects = $conn->query("SELECT * FROM subjects WHERE department_id = $dept_id ORDER BY name ASC");

// Fetch recent questions (filtered by department)
$recent_questions = $conn->query("SELECT q.*, s.name as subject_name 
                                  FROM questions q 
                                  JOIN subjects s ON q.subject_id = s.id 
                                  WHERE q.department_id = $dept_id
                                  ORDER BY q.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions | Smart Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
    <style>
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-weight: 600;
        }

        .alert-success {
            background: var(--emerald-light);
            color: var(--emerald);
        }

        .question-item {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .question-item:last-child {
            border-bottom: none;
        }

        .q-text {
            font-weight: 600;
            color: var(--text-main);
        }

        .q-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            gap: 12px;
        }

        .badge-sub {
            background: var(--primary-light);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
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
                <a href="manage_questions.php" class="nav-item active">
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
                    <h2>Manage Questions</h2>
                    <p>Build your question bank for <strong><?php echo $_SESSION['admin_dept_name']; ?></strong>.</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard-main-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3>Add New Question</h3>
                        </div>
                        <form method="POST">
                            <div class="form-group">
                                <label>Select Subject</label>
                                <select name="subject_id" class="form-control" required>
                                    <option value="">Choose Subject...</option>
                                    <?php while ($sub = $subjects->fetch_assoc()): ?>
                                        <option value="<?php echo $sub['id']; ?>">
                                            <?php echo $sub['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Question Text</label>
                                <textarea name="question_text" class="form-control" rows="3"
                                    placeholder="Enter your question here..." required></textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group"><label>Option 1</label><input type="text" name="option1"
                                        class="form-control" required></div>
                                <div class="form-group"><label>Option 2</label><input type="text" name="option2"
                                        class="form-control" required></div>
                                <div class="form-group"><label>Option 3</label><input type="text" name="option3"
                                        class="form-control" required></div>
                                <div class="form-group"><label>Option 4</label><input type="text" name="option4"
                                        class="form-control" required></div>
                            </div>

                            <div class="form-group" style="width: 50%;">
                                <label>Correct Option</label>
                                <select name="correct_option" class="form-control" required>
                                    <option value="1">Option 1</option>
                                    <option value="2">Option 2</option>
                                    <option value="3">Option 3</option>
                                    <option value="4">Option 4</option>
                                </select>
                            </div>

                            <button type="submit" name="add_question" class="btn btn-primary" style="width:100%;">Save
                                Question</button>
                        </form>
                    </div>

                    <div class="card" style="height: fit-content;">
                        <div class="card-header">
                            <h3>Recently Added</h3>
                        </div>
                        <div class="question-list">
                            <?php if ($recent_questions->num_rows > 0): ?>
                                <?php while ($q = $recent_questions->fetch_assoc()): ?>
                                    <div class="question-item">
                                        <span class="q-text">
                                            <?php echo $q['question_text']; ?>
                                        </span>
                                        <div class="q-meta">
                                            <span class="badge-sub">
                                                <?php echo $q['subject_name']; ?>
                                            </span>
                                            <span>#
                                                <?php echo $q['id']; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="padding: 20px; text-align: center; color: var(--text-muted);">No questions yet.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../../public/js/admin_dashboard.js"></script>
</body>

</html>