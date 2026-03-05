<?php
require_once "c:/xampp/htdocs/SLEMS/config/database.php";

echo "Starting database updates...\n";

// 1. Add subject_id to results table
$check_column = $conn->query("SHOW COLUMNS FROM results LIKE 'subject_id'");
if ($check_column->num_rows == 0) {
    echo "Adding subject_id to results table...\n";
    $sql = "ALTER TABLE results ADD COLUMN subject_id INT NULL AFTER user_id";
    if ($conn->query($sql)) {
        echo "Successfully added subject_id to results.\n";
    } else {
        echo "Error adding subject_id: " . $conn->error . "\n";
    }
} else {
    echo "subject_id column already exists in results.\n";
}

// 2. Create study_plans table
echo "Checking for study_plans table...\n";
$sql = "CREATE TABLE IF NOT EXISTS study_plans (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    recommended_hours INT(5) NOT NULL,
    study_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Successfully created or verified study_plans table.\n";
} else {
    echo "Error creating study_plans table: " . $conn->error . "\n";
}

echo "Database updates complete.\n";
?>