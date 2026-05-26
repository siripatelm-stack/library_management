<?php
session_start();

if(!isset($_SESSION['admin_username'])){

    header("Location: ./admin_login.php");
    exit();
}

require_once("../config/db.php");

$id = intval($_POST['id']);
$book = mysqli_real_escape_string($conn, $_POST['book_name']);
$author = mysqli_real_escape_string($conn, $_POST['author']);
$total = intval($_POST['total_quantity']);
$available = intval($_POST['available_quantity']);
$isbn = mysqli_real_escape_string($conn, $_POST['isbn']);

mysqli_query($conn, "
UPDATE books SET
book_name='$book',
author='$author',
total_quantity='$total',
available_quantity='$available',
isbn='$isbn'
WHERE id=$id
");
?>