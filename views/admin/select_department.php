<?php
session_start();
require_once "../../config/database.php";
require_once "../../models/Department.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$deptModel = new Department($conn);
$departments = $deptModel->getAllDepartments();

if (isset($_POST['select_dept'])) {
    $dept_id = intval($_POST['department_id']);
    $dept = $deptModel->getById($dept_id);
    if ($dept) {
        $_SESSION['admin_dept_id'] = $dept['id'];
        $_SESSION['admin_dept_name'] = $dept['department_name'];
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Department | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --bg-main: #f8fafc;
        }

        body {
            background-color: var(--bg-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .selection-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 24px;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: #1e293b;
        }

        p {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        .dept-grid {
            display: grid;
            gap: 12px;
            margin-bottom: 24px;
        }

        .dept-option {
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            position: relative;
        }

        .dept-option:hover {
            border-color: var(--primary);
            background: #f5f3ff;
        }

        .dept-option input {
            position: absolute;
            opacity: 0;
        }

        .dept-option.selected {
            border-color: var(--primary);
            background: #f5f3ff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .dept-icon {
            width: 32px;
            height: 32px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dept-name {
            font-weight: 600;
            color: #1e293b;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="selection-card">
        <div class="logo-box"><i class="fas fa-university"></i></div>
        <h1>Select Department</h1>
        <p>Choose a department to manage</p>

        <form method="POST">
            <div class="dept-grid">
                <?php foreach ($departments as $dept): ?>
                    <label class="dept-option" onclick="selectDept(this)">
                        <input type="radio" name="department_id" value="<?php echo $dept['id']; ?>" required>
                        <div class="dept-icon"><i class="fas fa-folder"></i></div>
                        <span class="dept-name">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="select_dept" class="btn-submit">Enter Dashboard</button>
        </form>
    </div>

    <script>
        function selectDept(el) {
            document.querySelectorAll('.dept-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');
        }
    </script>
</body>

</html>