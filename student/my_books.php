<?php
session_start();

if(!isset($_SESSION['student_usn'])){
    header("Location: login.php");
    exit();
}

require_once("../config/db.php");

$student_usn = $_SESSION['student_usn'];

$result = mysqli_query($conn, "
    SELECT book_name, issue_datetime, return_due_date, status
    FROM issued_books
    WHERE usn='$student_usn'
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books</title>
    <link rel="stylesheet" href="./student.css">
    <style>
        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status.available { background: #22c55e; color: white; }
        .status.low       { background: #f59e0b; color: white; }
        .status.out       { background: #ef4444; color: white; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        .empty-state div { font-size: 40px; margin-bottom: 10px; }
    </style>
</head>

<body>
<div class="dashboard">
    <?php require_once("student_sidebar.php"); ?>

    <div class="main">
        <div class="header">
            <h2>📖 My Books</h2>
            <p>Issued Books Details</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Return Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $today    = strtotime(date('Y-m-d'));
                $sl       = 1;
                $has_rows = false;

                while($row = mysqli_fetch_assoc($result)):
                    $has_rows   = true;
                    $status     = strtolower($row['status']);
                    $due        = strtotime(date('Y-m-d', strtotime($row['return_due_date'])));
                    $is_overdue = ($status == 'issued' && $due < $today);
                ?>
                <tr>
                    <td><?= $sl++ ?></td>
                    <td><?= htmlspecialchars($row['book_name']) ?></td>
                    <td><?= htmlspecialchars($row['issue_datetime']) ?></td>
                    <td><?= htmlspecialchars($row['return_due_date']) ?></td>
                    <td>
                        <?php if($status == 'returned'): ?>
                            <span class="status available">Returned</span>
                        <?php elseif($is_overdue): ?>
                            <span class="status out">Overdue</span>
                        <?php else: ?>
                            <span class="status low">Issued</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if(!$has_rows): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div>📭</div>
                            <p>You have not borrowed any books yet.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>