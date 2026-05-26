<?php

require_once("../config/db.php");

if(!isset($_GET['id'])){
    header("Location: issued_books.php");
    exit();
}

$id = intval($_GET['id']);

$issue = mysqli_fetch_assoc(
mysqli_query($conn, "SELECT id, isbn, status FROM issued_books WHERE id='$id'")
);

if(!$issue){
    header("Location: issued_books.php");
    exit();
}

if(strtolower($issue['status']) !== 'issued'){
    header("Location: issued_books.php");
    exit();
}

$isbn = mysqli_real_escape_string($conn, $issue['isbn']);

mysqli_query($conn, "UPDATE issued_books SET status='returned' WHERE id='$id'");
mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity + 1 WHERE isbn='$isbn'");

header("Location: issued_books.php");
exit();
