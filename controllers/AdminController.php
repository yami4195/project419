<?php

class AdminController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function viewResults()
    {
        $resultModel = new Result($this->db);
        $results = $resultModel->getAllResults();
        return $results;
    }

    public function updateTimeLimit($subject_id, $limit)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->updateTimeLimit($subject_id, $limit);
    }

    public function addSubject($name, $code = null, $dept_id = null, $description = null)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->addSubject($name, $code, $dept_id, $description);
    }

    public function updateSubject($id, $name, $code, $dept_id, $description)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->updateSubject($id, $name, $code, $dept_id, $description);
    }

    public function deleteSubject($id)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->deleteSubject($id);
    }

    public function getAllSubjects()
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->getAll();
    }

    public function getSubjectsByDepartment($dept_id)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->getByDepartment($dept_id);
    }
}
