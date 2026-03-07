<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";
require_once "../../models/Department.php";
require_once "../../models/Subject.php";
require_once "check_dept.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$adminController = new AdminController($conn);
$deptModel = new Department($conn);
$subjectModel = new Subject($conn);

$departments = $deptModel->getAllDepartments();
$message = "";
$status = "success";

// Handle Add Subject
if (isset($_POST['add_subject'])) {
    $name = $_POST['subject_name'];
    $code = $_POST['subject_code'];
    $dept_id = $_SESSION['admin_dept_id']; // Forced to current dept
    $desc = $_POST['description'];

    if ($adminController->addSubject($name, $code, $dept_id, $desc)) {
        $message = "Subject added successfully!";
    } else {
        $message = "Error adding subject: " . $conn->error;
        $status = "error";
    }
}

// Handle Update Subject
if (isset($_POST['update_subject'])) {
    $id = $_POST['subject_id'];
    $name = $_POST['subject_name'];
    $code = $_POST['subject_code'];
    $dept_id = $_SESSION['admin_dept_id']; // Keep it in current dept
    $desc = $_POST['description'];

    if ($adminController->updateSubject($id, $name, $code, $dept_id, $desc)) {
        $message = "Subject updated successfully!";
    } else {
        $message = "Error updating subject.";
        $status = "error";
    }
}

// Handle Delete Subject
if (isset($_GET['delete_id'])) {
    if ($adminController->deleteSubject($_GET['delete_id'])) {
        $message = "Subject deleted!";
    } else {
        $message = "Error deleting subject.";
        $status = "error";
    }
}

// Always filter by session department
$selected_dept = $_SESSION['admin_dept_id'];
$subjects = $adminController->getSubjectsByDepartment($selected_dept);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects | Smart Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin_dashboard.css">
    <style>
        .subject-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .subject-table th {
            padding: 12px 20px;
            text-align: left;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .subject-table tr {
            background: white;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            transition: all 0.2s;
        }

        .subject-table tr:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .subject-table td {
            padding: 20px;
        }

        .subject-table tr td:first-child {
            border-radius: var(--radius) 0 0 var(--radius);
        }

        .subject-table tr td:last-child {
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-dept {
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-code {
            background: var(--bg-main);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit {
            background: var(--amber-light);
            color: var(--amber);
        }

        .btn-delete {
            background: var(--rose-light);
            color: var(--rose);
        }

        .btn-edit:hover {
            background: var(--amber);
            color: white;
        }

        .btn-delete:hover {
            background: var(--rose);
            color: white;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
            background: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            padding: 32px;
            border-radius: var(--radius-lg);
            width: 500px;
            max-width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
        }

        .search-wrapper {
            display: flex;
            gap: 12px;
            align-items: center;
            background: white;
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            flex: 1;
        }

        .search-wrapper input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
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
                <a href="manage_subject.php" class="nav-item active">
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
                <div class="filter-section">
                    <div>
                        <h2 style="font-weight: 800; font-size: 1.75rem;">Manage Subjects</h2>
                        <p style="color: var(--text-muted);">Manage subjects for
                            <strong><?php echo $_SESSION['admin_dept_name']; ?></strong>.
                        </p>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button class="btn btn-primary" onclick="openModal('addSubjectModal')">
                            <i class="fas fa-plus"></i> Add Subject
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert <?php echo $status == 'success' ? 'alert-success' : 'alert-error'; ?>"
                        style="margin-bottom: 24px; padding: 16px; border-radius: 12px; background: <?php echo $status == 'success' ? 'var(--emerald-light)' : 'var(--rose-light)'; ?>; color: <?php echo $status == 'success' ? 'var(--emerald)' : 'var(--rose)'; ?>; font-weight: 600;">
                        <i
                            class="fas <?php echo $status == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <table class="subject-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>Department</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subjects)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">No
                                        subjects found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subjects as $s): ?>
                                    <tr>
                                        <td><span class="badge badge-code"><?php echo $s['subject_code'] ?: 'N/A'; ?></span>
                                        </td>
                                        <td><span
                                                style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($s['name']); ?></span>
                                        </td>
                                        <td><span
                                                class="badge badge-dept"><?php echo htmlspecialchars($s['department_name'] ?: 'None'); ?></span>
                                        </td>
                                        <td
                                            style="color: var(--text-muted); font-size: 0.85rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($s['description'] ?: 'No description provided.'); ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="btn-icon btn-edit" title="Edit"
                                                    onclick="editSubject(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete_id=<?php echo $s['id']; ?>" class="btn-icon btn-delete"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this subject?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Subject Modal -->
    <div id="addSubjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Subject</h3>
                <button class="close-modal" onclick="closeModal('addSubjectModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?php echo $_SESSION['admin_dept_name']; ?>"
                        disabled>
                    <input type="hidden" name="department_id" value="<?php echo $_SESSION['admin_dept_id']; ?>">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" class="form-control"
                        placeholder="e.g. Artificial Intelligence" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" class="form-control" placeholder="e.g. CS401" required>
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Description (Optional)</label>
                    <textarea name="description" class="form-control" style="height: 100px; resize: none;"
                        placeholder="Provide a brief overview..."></textarea>
                </div>
                <button type="submit" name="add_subject" class="btn btn-primary"
                    style="width: 100%; padding: 14px;">Create Subject</button>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div id="editSubjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Subject</h3>
                <button class="close-modal" onclick="closeModal('editSubjectModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="subject_id" id="edit_id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?php echo $_SESSION['admin_dept_name']; ?>"
                        disabled>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" id="edit_code" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" class="form-control"
                        style="height: 100px; resize: none;"></textarea>
                </div>
                <button type="submit" name="update_subject" class="btn btn-primary"
                    style="width: 100%; padding: 14px;">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function editSubject(subject) {
            document.getElementById('edit_id').value = subject.id;
            document.getElementById('edit_name').value = subject.name;
            document.getElementById('edit_code').value = subject.subject_code;
            document.getElementById('edit_description').value = subject.description;
            openModal('editSubjectModal');
        }

        window.onclick = function (event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>