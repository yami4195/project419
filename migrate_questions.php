<?php
$c = new mysqli('localhost', 'root', '', 'smart_learning');
if ($c->connect_error)
    die($c->connect_error);

$c->query("ALTER TABLE questions ADD COLUMN IF NOT EXISTS department_id INT AFTER subject_id");
$c->query("ALTER TABLE questions ADD CONSTRAINT fk_question_dept FOREIGN KEY (department_id) REFERENCES departments(id)");

echo "Question table updated successfully";
$c->close();
?>