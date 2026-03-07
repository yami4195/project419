<?php

class AuthController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function register($data)
    {
        $name = $this->db->real_escape_string($data['name']);
        $username = $this->db->real_escape_string($data['username']);
        $email = $this->db->real_escape_string($data['email']);
        $password = $data['password'];
        $role = $this->db->real_escape_string($data['role'] ?? 'student');

        $department_id = isset($data['department_id']) && !empty($data['department_id'])
            ? intval($data['department_id'])
            : null;

        // Validation for student department
        if ($role === 'student' && !$department_id) {
            return ['success' => false, 'message' => 'Department selection is required for students.'];
        }

        // 1. Check if email or username already exists
        $check = $this->db->query("SELECT id FROM users WHERE email = '$email' OR username = '$username'");
        if ($check && $check->num_rows > 0) {
            return ['success' => false, 'message' => 'Email or Username already taken.'];
        }

        // 2. Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user
        $dept_val = $department_id ? $department_id : "NULL";
        $sql = "INSERT INTO users (name, username, email, password, role, department_id) 
                VALUES ('$name', '$username', '$email', '$hashed_password', '$role', $dept_val)";

        if ($this->db->query($sql)) {
            return ['success' => true, 'message' => 'Registration successful! You can now log in.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed: ' . $this->db->error];
        }
    }
}
