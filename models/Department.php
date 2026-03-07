<?php

class Department
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Get all departments from the database
     */
    public function getAllDepartments()
    {
        $sql = "SELECT * FROM departments ORDER BY department_name ASC";
        $result = $this->db->query($sql);
        $departments = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }
        }

        return $departments;
    }

    /**
     * Get a specific department by ID
     */
    public function getById($id)
    {
        $id = intval($id);
        $sql = "SELECT * FROM departments WHERE id = $id";
        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }
}
