<?php

class StudyPlannerController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getMyStudyPlan($user_id)
    {
        $studyPlanModel = new StudyPlan($this->db);
        return $studyPlanModel->getByUserId($user_id);
    }

    public function generatePlan($user_id)
    {
        $resultModel = new Result($this->db);
        $studyPlanModel = new StudyPlan($this->db);
        $subjectModel = new Subject($this->db);

        // 1. Get performance per subject
        $stats = $resultModel->getAverageScoresPerSubject($user_id);

        // 2. Clear old plans
        $studyPlanModel->deleteOldPlans($user_id);

        // 3. Generate new recommendations
        // We prioritize subjects with average < 70%
        $recommendations = [];
        $today = date('Y-m-d');
        $dayCounter = 1;

        foreach ($stats as $stat) {
            if ($stat['average_percentage'] < 70) {
                // Recommendation logic: 
                // < 40% -> 4 hours
                // < 60% -> 3 hours
                // < 70% -> 2 hours
                $hours = 2;
                if ($stat['average_percentage'] < 40)
                    $hours = 4;
                elseif ($stat['average_percentage'] < 60)
                    $hours = 3;

                $studyDate = date('Y-m-d', strtotime("+$dayCounter day"));

                $studyPlanModel->savePlan(
                    $user_id,
                    $stat['subject_id'],
                    $hours,
                    $studyDate
                );
                $dayCounter++;
            }
        }

        // If no performance data or all subjects mastered, suggest all subjects with 1 hour
        if ($dayCounter == 1) {
            $allSubjects = $subjectModel->getAll();
            foreach ($allSubjects as $sub) {
                $studyDate = date('Y-m-d', strtotime("+$dayCounter day"));
                $studyPlanModel->savePlan($user_id, $sub['id'], 1, $studyDate);
                $dayCounter++;
                if ($dayCounter > 7)
                    break; // Limit to a week
            }
        }

        return true;
    }
}
