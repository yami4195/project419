<?php
require_once "c:/xampp/htdocs/SLEMS/config/database.php";

$tables = ['users', 'subjects', 'questions', 'options', 'results', 'exams', 'study_plans'];

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    try {
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "Table does not exist or error: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
