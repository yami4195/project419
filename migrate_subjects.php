<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "smart_learning";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Update subjects table
$queries = [
    "ALTER TABLE subjects ADD COLUMN IF NOT EXISTS subject_code VARCHAR(50) AFTER name",
    "ALTER TABLE subjects ADD COLUMN IF NOT EXISTS department_id INT AFTER subject_code",
    "ALTER TABLE subjects ADD COLUMN IF NOT EXISTS description TEXT AFTER department_id",
    "ALTER TABLE subjects ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE subjects ADD CONSTRAINT fk_subject_dept FOREIGN KEY (department_id) REFERENCES departments(id)"
];

foreach ($queries as $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Executed: $sql\n";
        } else {
            echo "Info: " . $conn->error . " (SQL: $sql)\n";
        }
    } catch (Exception $e) {
        echo "Handled error: " . $e->getMessage() . "\n";
    }
}

$conn->close();
?>