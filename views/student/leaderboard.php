<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../../config/database.php";
require_once "../../models/Result.php";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$resultModel = new Result($conn);
$topPerformers = $resultModel->getTopPerformers(10);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Slems - Leaderboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #666;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .rank {
            font-weight: bold;
            color: #4CAF50;
            width: 60px;
        }

        .rank-1 {
            color: #FFD700;
            font-size: 1.2rem;
        }

        /* Gold */
        .rank-2 {
            color: #C0C0C0;
            font-size: 1.1rem;
        }

        /* Silver */
        .rank-3 {
            color: #CD7F32;
            font-size: 1.05rem;
        }

        /* Bronze */

        .score {
            font-weight: bold;
            text-align: right;
            color: #2196F3;
        }

        .btn {
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: #555;
        }

        .trophy {
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>🏆 Student Leaderboard 🏆</h2>

        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th style="text-align: right;">Average Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topPerformers)): ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($topPerformers as $performer): ?>
                        <tr>
                            <td class="rank rank-<?php echo $rank; ?>">
                                <?php
                                if ($rank == 1)
                                    echo "🥇 ";
                                elseif ($rank == 2)
                                    echo "🥈 ";
                                elseif ($rank == 3)
                                    echo "🥉 ";
                                else
                                    echo "#" . $rank;
                                ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($performer['student_name']); ?>
                            </td>
                            <td class="score">
                                <?php echo round($performer['average_percentage'], 2); ?>%
                            </td>
                        </tr>
                        <?php $rank++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No performance data available yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <a href="Dashboard.php" class="btn">Back to Dashboard</a>
    </div>

</body>

</html>