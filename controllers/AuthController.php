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

        // 1. Check if email or username already exists
        $check = $this->db->query("SELECT id FROM users WHERE email = '$email' OR username = '$username'");
        if ($check && $check->num_rows > 0) {
            return ['success' => false, 'message' => 'Email or Username already taken.'];
        }

        // 2. Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user
        $sql = "INSERT INTO users (name, username, email, password, role) 
                VALUES ('$name', '$username', '$email', '$hashed_password', '$role')";

        if ($this->db->query($sql)) {
            return ['success' => true, 'message' => 'Registration successful! You can now log in.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed: ' . $this->db->error];
        }
    }
}
