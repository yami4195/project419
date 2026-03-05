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

    public function addSubject($name)
    {
        $subjectModel = new Subject($this->db);
        return $subjectModel->addSubject($name);
    }
}
