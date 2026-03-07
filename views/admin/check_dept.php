<?php
if ($_SESSION['user_role'] == 'admin' && !isset($_SESSION['admin_dept_id'])) {
    header("Location: select_department.php");
    exit();
}
?>