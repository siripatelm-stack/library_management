<?php

session_start();

if(!isset($_SESSION['admin_username'])){

    header("Location: ./admin_login.php");
    exit();
}

require_once("../config/db.php");

/* DELETE BOOK */

if(isset($_GET['delete_id'])){

$id = $_GET['delete_id'];

mysqli_query($conn,"
DELETE FROM books
WHERE book_id='$id'
");

header("Location: manage_books.php");

exit();
}

/* FETCH BOOKS */

$search = $_GET['search'] ?? '';

$category = $_GET['category'] ?? '';

$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM books WHERE 1";

/* SEARCH */

if($search != ''){

$sql .= "
AND (
book_name LIKE '%$search%'
OR isbn LIKE '%$search%'
)
";
}

/* CATEGORY */

if($category != ''){
$sql .= "
AND category='$category'
";
}
/* STATUS */
if($status != ''){
$sql .= "
AND status='$status'
";
}

$result = mysqli_query($conn,$sql);

/* TOTAL BOOKS */

$total_books = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT SUM(quantity) as total
FROM books
")
)['total'];

/* AVAILABLE BOOKS */

$issued_books = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as issued
FROM issued_books
WHERE status='issued'
")
)['issued'];
$available_quantity = max(0, $total_books - $issued_books);

/* OVERDUE BOOKS */
$overdue_books = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as overdue
FROM issued_books
WHERE return_due_date < CURDATE()
AND status='issued'
"
))['overdue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
content="width=device-width, initial-scale=1.0">
<title>Manage Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<!-- SIDEBAR -->
<?php require_once("sidebar.php"); ?>
<!-- MAIN -->
<div class="main">
<!-- HEADER -->
<div class="header">
<div class="header-left">
<h1>📘 Manage Books</h1>
</div>
<a href="add_book.php" class="add-btn">
<button style="color:blue">
+ Add New Book
</button>
</a>
</div>
<!-- CARDS -->
<div class="cards">
<div class="card">
<h3>Total Books</h3>
<p><?php echo $total_books; ?></p>
</div>
<div class="card">
<h3>Available Books</h3>
<p><?php echo $available_quantity; ?></p>
</div>
<div class="card">
<h3>Issued Books</h3>
<p><?php echo $issued_books; ?></p>
</div>
<div class="card">
<h3>Overdue Books</h3>
<p><?php echo $overdue_books; ?></p>
</div>
</div>
<!-- SEARCH -->
<form method="GET" class="search-box">
<input type="text" name="search" placeholder="Search by book name or ISBN">
<select name="category">
<option value="">All Categories</option>
<option value="MATHEMATICS">MATHEMATICS</option>
<option value="DSA">DSA</option>
<option value="OS">OS</option>
<option value="JAVA">JAVA</option>
<option value="AUTOMATA">AUTOMATA</option>
<option value="MANAGEMENT">MANAGEMENT</option>
<option value="NETWORKING">NETWORKING</option>
<option value="COMPUTER ARCHITECTURE">COMPUTER ARCHITECTURE</option>
<option value="APTITUDE">APTITUDE</option>
<option value="MOBILE DEVELOPMENT">MOBILE DEVELOPMENT</option>
<option value="PROGRAMMING">PROGRAMMING</option>
<option value="SOFTWARE ENGINEERING">SOFTWARE ENGINEERING</option>
<option value="CLOUD COMPUTING">CLOUD COMPUTING</option>
<option value="GENERAL">GENERAL</option>
<option value="DBMS">DBMS</option>
<option value="WEB TECHNOLOGY">WEB TECHNOLOGY</option>
</select>

<select name="status">
<option value="">All Status</option>
<option value="Available">Available</option>
<option value="Low Stock">Low Stock</option>
<option value="Out of Stock">Out of Stock</option>
</select>
<button type="submit">Search</button>
</form>
<!-- TABLE -->
<div class="table-container">
<table>
<tr>
<th>SL NO</th>
<th>BOOK NAME</th>
<th>ISBN</th>
<th>TOTAL</th>
<th>AVAILABLE</th>
<th>STATUS</th>
<th>EDIT</th>
<th>DELETE</th>
</tr>
<?php $sl = 1; ?>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?php echo $sl++; ?></td>
<td><?php echo $row['book_name']; ?></td>
<td><?php echo $row['isbn']; ?></td>
<td><?php echo $row['quantity']; ?></td>
<td><?php echo $row['available_quantity']; ?></td>
<td>
<?php
if($row['available_quantity'] > 2){
echo "Available";
}
elseif($row['available_quantity'] > 0){
echo "Low Stock";
}
else{
echo "<span class='status out'>Out of Stock</span>";
}

if($row['quantity']<2){
    echo " <br> <span style='color:red; font-weight:bold;'> Restock recommended! </span>";
}

?>
</td>
<td>
<a href="edit_book.php?id=<?php echo $row['book_id']; ?>">
<button class="edit-btn">Edit</button>
</a>
</td>
<td>
<a href="manage_books.php?delete_id=<?php echo $row['book_id']; ?>"
onclick="return confirm('Delete this book?')">
<button class="delete-btn">Delete</button>
</a>
</td>
</tr>
<?php } ?>