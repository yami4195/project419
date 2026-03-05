<?php

class Result
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllResults()
    {
        $sql = "SELECT r.*, u.name as student_name 
                FROM results r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC";
        $result = $this->conn->query($sql);

        $results = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        }
        return $results;
    }

    public function getAverageScoresPerSubject($user_id)
    {
        $user_id = intval($user_id);
        $sql = "SELECT s.id as subject_id, s.name as subject_name, 
                       AVG((r.score / r.total) * 100) as average_percentage
                FROM results r
                JOIN subjects s ON r.subject_id = s.id
                WHERE r.user_id = $user_id
                GROUP BY s.id
                ORDER BY average_percentage ASC";

        $result = $this->conn->query($sql);
        $stats = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
        }
        return $stats;
    }

    public function getGeneralStats($user_id)
    {
        $user_id = intval($user_id);
        $sql = "SELECT 
                    COUNT(*) as total_exams,
                    AVG((score / total) * 100) as average_score,
                    MAX((score / total) * 100) as best_score
                FROM results 
                WHERE user_id = $user_id";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function getTopPerformers($limit = 10)
    {
        $limit = intval($limit);
        $sql = "SELECT u.name as student_name, 
                       AVG((r.score / r.total) * 100) as average_percentage
                FROM results r
                JOIN users u ON r.user_id = u.id
                GROUP BY u.id
                ORDER BY average_percentage DESC
                LIMIT $limit";

        $result = $this->conn->query($sql);
        $topPerformers = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $topPerformers[] = $row;
            }
        }
        return $topPerformers;
    }

    public function getLatestAttempt($user_id, $subject_id)
    {
        $user_id = intval($user_id);
        $subject_id = intval($subject_id);
        $sql = "SELECT MAX(attempt) as total_attempts, MAX((score/total)*100) as best_percentage 
                FROM results 
                WHERE user_id = $user_id AND subject_id = $subject_id";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_assoc() : ['total_attempts' => 0, 'best_percentage' => 0];
    }

    public function saveResult($user_id, $subject_id, $score, $total, $attempt)
    {
        $user_id = intval($user_id);
        $subject_id = intval($subject_id);
        $score = intval($score);
        $total = intval($total);
        $attempt = intval($attempt);

        $sql = "INSERT INTO results (user_id, subject_id, score, total, attempt) 
                VALUES ($user_id, $subject_id, $score, $total, $attempt)";
        return $this->conn->query($sql);
    }
}
