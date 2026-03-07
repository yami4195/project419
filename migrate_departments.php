<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "smart_learning";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create departments table
$c_table = "CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
)";
if ($conn->query($c_table)) {
    echo "Table 'departments' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// 2. Insert departments
$departments = [
    'Computer Science',
    'Software Engineering',
    'Information Technology',
    'Civil Engineering',
    'Architecture',
    'Electrical Engineering',
    'Mechanical Engineering',
    'Business Administration',
    'Other'
];

foreach ($departments as $dept) {
    $check = $conn->query("SELECT id FROM departments WHERE department_name = '$dept'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO departments (department_name) VALUES ('$dept')");
    }
}
echo "Initial department data inserted.\n";

// 3. Update users table
$alt_table = "ALTER TABLE users ADD COLUMN department_id INT NULL";
if ($conn->query($alt_table)) {
    echo "Column 'department_id' added to users table.\n";
    $conn->query("ALTER TABLE users ADD FOREIGN KEY (department_id) REFERENCES departments(id)");
    echo "Foreign key constraint added.\n";
} else {
    // If column already exists
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "Column 'department_id' already exists.\n";
    } else {
        echo "Error altering table: " . $conn->error . "\n";
    }
}

$conn->close();
?>