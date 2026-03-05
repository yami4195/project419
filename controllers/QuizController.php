<?php

class QuizController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getPerformanceData($user_id)
    {
        $resultModel = new Result($this->db);

        $stats = $resultModel->getGeneralStats($user_id);
        $subjectPerformance = $resultModel->getAverageScoresPerSubject($user_id);

        return [
            'stats' => $stats,
            'subject_performance' => $subjectPerformance
        ];
    }

    public function checkEligibility($user_id, $subject_id)
    {
        $resultModel = new Result($this->db);
        $latest = $resultModel->getLatestAttempt($user_id, $subject_id);

        $attempts = $latest['total_attempts'] ?? 0;
        $best = $latest['best_percentage'] ?? 0;

        if ($best >= 50) {
            return ['eligible' => false, 'message' => 'You have already passed this quiz.'];
        }

        if ($attempts >= 2) {
            return ['eligible' => false, 'message' => 'You have reached the maximum number of attempts (2) for this quiz.'];
        }

        return ['eligible' => true, 'next_attempt' => $attempts + 1];
    }

    public function submitQuiz($user_id, $subject_id, $answers)
    {
        $eligibility = $this->checkEligibility($user_id, $subject_id);
        if (!$eligibility['eligible']) {
            return ['success' => false, 'message' => $eligibility['message']];
        }

        $score = 0;
        $total = 0;

        foreach ($answers as $question_id => $selected_option_id) {
            $total++;
            $stmt = $this->db->prepare("SELECT is_correct FROM options WHERE id = ?");
            $stmt->bind_param("i", $selected_option_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            if ($row && $row['is_correct'] == 1) {
                $score++;
            }
        }

        $resultModel = new Result($this->db);
        $saved = $resultModel->saveResult($user_id, $subject_id, $score, $total, $eligibility['next_attempt']);

        return [
            'success' => $saved,
            'score' => $score,
            'total' => $total,
            'message' => $saved ? 'Quiz submitted successfully!' : 'Error saving results.'
        ];
    }
}
