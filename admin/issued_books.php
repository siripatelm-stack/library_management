<?php
session_start();
if(!isset($_SESSION['admin_username'])){
    header("Location: ./admin_login.php");
    exit();
}

require_once("../config/db.php");

$message = "";
$message_type = "";

// Handle return action
if(isset($_GET['action']) && $_GET['action'] == 'mark_returned' && isset($_GET['isbn'])){
    $returned_isbn = mysqli_real_escape_string($conn, $_GET['isbn']);
    $record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if($record_id > 0){
        mysqli_query($conn, "UPDATE issued_books SET status='returned' WHERE id='$record_id'");
    } else {
        mysqli_query($conn, "UPDATE issued_books SET status='returned' WHERE isbn='$returned_isbn' AND status='issued' ORDER BY id DESC LIMIT 1");
    }

    mysqli_query($conn, "UPDATE books SET quantity = quantity + 1 WHERE isbn='$returned_isbn'");

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=returned&isbn=" . urlencode($returned_isbn));
    exit();
}

// Success message after return
if(isset($_GET['success']) && $_GET['success'] == 'returned'){
    $message = "✅ Book returned successfully!";
    $message_type = "success";
}

// Scanned ISBN feedback
if(isset($_GET['scanned_isbn'])){
    $scanned_isbn = mysqli_real_escape_string($conn, $_GET['scanned_isbn']);
    $check = mysqli_query($conn, "SELECT book_name FROM books WHERE isbn='$scanned_isbn'");

    if(mysqli_num_rows($check) > 0){
        $book = mysqli_fetch_assoc($check);
        $message = "✅ Scanned ISBN: $scanned_isbn (" . $book['book_name'] . ") found!";
        $message_type = "success";
    } else {
        $message = "❌ ISBN: $scanned_isbn not found in database.";
        $message_type = "error";
    }
}

// Fetch issued books — fixed column ambiguity with alias
$query = "
    SELECT
        issued_books.id,
        issued_books.student_name,
        issued_books.usn,
        issued_books.isbn,
        issued_books.status,
        issued_books.issue_datetime,
        issued_books.return_due_date,
        books.book_name
    FROM issued_books
    INNER JOIN books ON books.isbn = issued_books.isbn
    ORDER BY issued_books.id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issued Books</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-issued {
            background: #22c55e;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-returned {
            background: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-overdue {
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .return-btn {
            background: #2563eb;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }
        .return-btn:hover {
            background: #1d4ed8;
        }
        .msg-success {
            padding: 14px 20px;
            background: #dcfce7;
            color: #166534;
            text-align: center;
            font-weight: bold;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .msg-error {
            padding: 14px 20px;
            background: #fee2e2;
            color: #991b1b;
            text-align: center;
            font-weight: bold;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
    </style>

    <script>
        function searchBooks(){
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.getElementById("issuedTable").getElementsByTagName("tr");
            for(let i = 1; i < rows.length; i++){
                rows[i].style.display = rows[i].innerText.toLowerCase().includes(input) ? "" : "none";
            }
        }

        // Auto hide message after 3 seconds
        window.onload = function(){
            const msg = document.getElementById("flashMsg");
            if(msg){
                setTimeout(() => msg.style.display = "none", 3000);
            }
        }
    </script>
</head>

<body>
<?php require_once("sidebar.php"); ?>

<div class="main">
    <div class="header">
        <h2>📖 Issued Books</h2>
    </div>

    <!-- Flash Message -->
    <?php if($message): ?>
    <div id="flashMsg" class="msg-<?= $message_type ?>">
        <?= $message ?>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="search-box">
        <input type="text" id="searchInput"
               placeholder="🔍 Search student name, USN, or book..."
               onkeyup="searchBooks()">
    </div>

    <!-- Table -->
    <div class="table-container">
        <table id="issuedTable">
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>USN</th>
                <th>Book</th>
                <th>ISBN</th>
                <th>Issue Date</th>
                <th>Return Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            $today = strtotime(date('Y-m-d'));
            while($row = mysqli_fetch_assoc($result)):
                $status = strtolower($row['status']);
                $due    = strtotime(date('Y-m-d', strtotime($row['return_due_date'])));
                $is_overdue = ($status == 'issued' && $due < $today);
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['usn']) ?></td>
                <td><?= htmlspecialchars($row['book_name']) ?></td>
                <td><?= htmlspecialchars($row['isbn']) ?></td>
                <td><?= $row['issue_datetime'] ?></td>
                <td><?= $row['return_due_date'] ?></td>

                <td>
                    <?php if($is_overdue): ?>
                        <span class="status-overdue">Overdue</span>
                    <?php elseif($status == 'returned'): ?>
                        <span class="status-returned">Returned</span>
                    <?php else: ?>
                        <span class="status-issued">Issued</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if($status == 'issued'): ?>
                        <!-- Fixed: button inside anchor is invalid HTML -->
                        <button class="return-btn"
                            onclick="window.location='admin_scanner.php?mode=return&id=<?= $row['id'] ?>'">
                            Return
                        </button>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>