<?php

class Subject
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $sql = "SELECT s.*, d.department_name 
                FROM subjects s 
                LEFT JOIN departments d ON s.department_id = d.id 
                ORDER BY s.name ASC";
        $result = $this->conn->query($sql);
        $subjects = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row;
            }
        }
        return $subjects;
    }

    public function getByDepartment($dept_id)
    {
        $dept_id = intval($dept_id);
        $sql = "SELECT s.*, d.department_name 
                FROM subjects s 
                LEFT JOIN departments d ON s.department_id = d.id 
                WHERE s.department_id = $dept_id 
                ORDER BY s.name ASC";
        $result = $this->conn->query($sql);
        $subjects = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row;
            }
        }
        return $subjects;
    }

    public function getById($id)
    {
        $id = intval($id);
        $result = $this->conn->query("SELECT * FROM subjects WHERE id = $id");
        return $result ? $result->fetch_assoc() : null;
    }

    public function updateTimeLimit($id, $limit)
    {
        $id = intval($id);
        $limit = intval($limit);
        return $this->conn->query("UPDATE subjects SET time_limit = $limit WHERE id = $id");
    }

    public function addSubject($name, $code = null, $dept_id = null, $description = null)
    {
        $name = $this->conn->real_escape_string($name);
        $code = $code ? "'" . $this->conn->real_escape_string($code) . "'" : "NULL";
        $dept_id = $dept_id ? intval($dept_id) : "NULL";
        $description = $description ? "'" . $this->conn->real_escape_string($description) . "'" : "NULL";

        // Find the lowest missing ID
        $sql = "SELECT (t1.id + 1) as missing_id 
                FROM subjects t1 
                LEFT JOIN subjects t2 ON t1.id + 1 = t2.id 
                WHERE t2.id IS NULL 
                UNION 
                SELECT 1 AS missing_id 
                WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE id = 1)
                ORDER BY missing_id ASC 
                LIMIT 1";

        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        $new_id = $row['missing_id'];

        $sql_insert = "INSERT INTO subjects (id, name, subject_code, department_id, description) 
                      VALUES ($new_id, '$name', $code, $dept_id, $description)";
        return $this->conn->query($sql_insert);
    }

    public function updateSubject($id, $name, $code, $dept_id, $description)
    {
        $id = intval($id);
        $name = $this->conn->real_escape_string($name);
        $code = $code ? "'" . $this->conn->real_escape_string($code) . "'" : "NULL";
        $dept_id = $dept_id ? intval($dept_id) : "NULL";
        $description = $description ? "'" . $this->conn->real_escape_string($description) . "'" : "NULL";

        $sql = "UPDATE subjects SET 
                name = '$name', 
                subject_code = $code, 
                department_id = $dept_id, 
                description = $description 
                WHERE id = $id";
        return $this->conn->query($sql);
    }

    public function deleteSubject($id)
    {
        $id = intval($id);
        return $this->conn->query("DELETE FROM subjects WHERE id = $id");
    }
}
