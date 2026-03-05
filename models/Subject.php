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
        $result = $this->conn->query("SELECT * FROM subjects ORDER BY name ASC");
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

    public function addSubject($name)
    {
        $name = $this->conn->real_escape_string($name);

        // Find the lowest missing ID
        // Logic: Find the first ID such that ID+1 does not exist in the table, starting check from 0 (to see if 1 is missing)
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

        $sql_insert = "INSERT INTO subjects (id, name) VALUES ($new_id, '$name')";
        return $this->conn->query($sql_insert);
    }
}
