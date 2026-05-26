<?php

session_start();

if(!isset($_SESSION['admin_username'])){

    header("Location: ./admin_login.php");
    exit();
}

require_once("../config/db.php");

$scanned_isbn = "";

if(isset($_GET['scanned_isbn'])){
    $scanned_isbn = $_GET['scanned_isbn'];
}

    
if (!isset($conn)) {
    if (isset($con)) {
        $conn = $con;
    } elseif (isset($link)) {
        $conn = $link;
    } elseif (isset($db)) {
        $conn = $db;
    } elseif (isset($connection)) {
        $conn = $connection;
    }
}

if (!isset($conn) || !$conn) {
    die('Database connection not established.');
}

/* ADD BOOK */

if(isset($_POST['add_book'])){

$book_name = $_POST['book_name'];
$isbn = $_POST['isbn'];
$quantity = $_POST['quantity'];
$category = $_POST['category'];

/* STATUS */

if($quantity > 2){

$status = "Available";

}
elseif($quantity > 0){

$status = "Low Stock";

}
else{

$status = "Out of Stock";

}

/* INSERT */

mysqli_query($conn,"

INSERT INTO books
(book_name,isbn,quantity,available_quantity,category,status)

VALUES
('$book_name','$isbn','$quantity','$quantity','$category','$status')");

/* REDIRECT */

header("Location: manage_books.php");

exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Book</title>

<link rel="stylesheet" href="style.css">

<style>

.form-container{

background:white;
padding:30px;
border-radius:15px;
width:500px;
margin:auto;
margin-top:40px;

}

.form-container h2{

margin-bottom:20px;

}

.form-container input,
.form-container select{

width:100%;
padding:12px;
margin-bottom:15px;
border:1px solid #ccc;
border-radius:8px;

}

.save-btn{

background:#2563eb;
color:white;
padding:12px;
border:none;
border-radius:8px;
cursor:pointer;
width:100%;

}

</style>

</head>

<body>

<?php require_once("sidebar.php"); ?>

<div class="main">

<div class="form-container">

<h2>📘 Add New Book</h2>

<form method="POST">

<input
type="text"
name="book_name"
placeholder="Book Name"
required>

<!-- Inside <form method="POST"> in add_book.php -->

<input 
    type="text" 
    name="isbn" 
    id="isbn_field"
    placeholder="ISBN" 
    value="<?php echo $scanned_isbn; ?>"
    required>


<!-- Replace your current camera button with this specific code -->
<button type="button" onclick="window.location.href='admin_scanner.php';" class="scan-btn" style="background:#10b981; color:white; padding:12px; border:none; border-radius:8px; cursor:pointer; width:100%; margin-bottom:15px;">
    📷 Open Camera to Scan ISBN
</button>



<input
type="number"
name="quantity"
placeholder="Quantity"
required>

<select name="category" required>

<option value="">Select Category</option>

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
<option value="Self-Help">Self-Help</option>
</option>
</select>

<button
type="submit"
name="add_book"
class="save-btn">

Save Book

</button>

</form>

</div>

</div>

</body>
</html>