<?php

require_once("../config/db.php");

/* GET ID */

$id = $_GET['id'] ?? '';

/* FETCH BOOK */

$result = mysqli_query($conn,"
SELECT * FROM books
WHERE book_id='$id'
");

$row = mysqli_fetch_assoc($result);

/* UPDATE */

if(isset($_POST['update'])){

$book_name = $_POST['book_name'];

$isbn = $_POST['isbn'];

$quantity = $_POST['quantity'];

$category = $_POST['category'];

/* STATUS */

if($quantity == 0){

$status = "Out of Stock";

}
elseif($quantity <= 2){

$status = "Low Stock";

}
else{

$status = "Available";

}

/* UPDATE QUERY */

mysqli_query($conn,"

UPDATE books

SET

book_name='$book_name',

isbn='$isbn',

quantity='$quantity',

category='$category',

status='$status'

WHERE book_id='$id'

");

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

<title>Edit Book</title>

<link rel="stylesheet" href="style.css">

<style>

.form-container{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
max-width:600px;
margin:auto;
}

.form-container h2{
margin-bottom:20px;
}

.form-container form{
display:flex;
flex-direction:column;
gap:15px;
}

.form-container input,
.form-container select{
padding:12px;
border:1px solid #ddd;
border-radius:10px;
}

.update-btn{
background:#6c4ce4;
color:white;
padding:12px;
border:none;
border-radius:10px;
cursor:pointer;
font-size:16px;
}

</style>

</head>

<body>

<?php require_once("sidebar.php"); ?>

<div class="main">

<div class="form-container">

<h2>✏ Edit Book</h2>

<form method="POST">

<input
type="text"
name="book_name"
value="<?php echo $row['book_name']; ?>"
required>

<input
type="text"
name="isbn"
value="<?php echo $row['isbn']; ?>"
required>

<input
type="number"
name="quantity"
value="<?php echo $row['quantity']; ?>"
required>

<select name="category">

<option value="<?php echo $row['category']; ?>">
<?php echo $row['category']; ?>
</option>

<option value="MATHEMATICS">MATHEMATICS</option>
<option value="DSA">DSA</option>
<option value="OS">OS</option>
<option value="JAVA">JAVA</option>
<option value="AUTOMATA">AUTOMATA</option>
<option value="MANAGEMENT">MANAGEMENT</option>
<option value="NETWORKING">NETWORKING</option>
<option value="COMPUTER ARCHITECTURE">
COMPUTER ARCHITECTURE
</option>
<option value="APTITUDE">APTITUDE</option>
<option value="MOBILE DEVELOPMENT">
MOBILE DEVELOPMENT
</option>
<option value="PROGRAMMING">PROGRAMMING</option>
<option value="SOFTWARE ENGINEERING">
SOFTWARE ENGINEERING
</option>
<option value="CLOUD COMPUTING">
CLOUD COMPUTING
</option>
<option value="GENERAL">GENERAL</option>
<option value="DBMS">DBMS</option>
<option value="WEB TECHNOLOGY">WEB TECHNOLOGY</option>
</select>

<button
type="submit"
name="update"
class="update-btn">

Update Book

</button>

</form>

</div>

</div>

</body>

</html>