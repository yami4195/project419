<?php

class StudyPlan
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function savePlan($user_id, $subject_id, $hours, $date)
    {
        $user_id = intval($user_id);
        $subject_id = intval($subject_id);
        $hours = intval($hours);
        $date = $this->conn->real_escape_string($date);

        $sql = "INSERT INTO study_plans (user_id, subject_id, recommended_hours, study_date) 
                VALUES ($user_id, $subject_id, $hours, '$date')";
        return $this->conn->query($sql);
    }

    public function deleteOldPlans($user_id)
    {
        $user_id = intval($user_id);
        return $this->conn->query("DELETE FROM study_plans WHERE user_id = $user_id");
    }

    public function getByUserId($user_id)
    {
        $user_id = intval($user_id);
        $sql = "SELECT sp.*, s.name as subject_name 
                FROM study_plans sp 
                JOIN subjects s ON sp.subject_id = s.id 
                WHERE sp.user_id = $user_id 
                ORDER BY sp.study_date ASC";
        $result = $this->conn->query($sql);
        $plans = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $plans[] = $row;
            }
        }
        return $plans;
    }
}
