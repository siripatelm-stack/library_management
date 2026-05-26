<?php
session_start();

// Enable clean error messages to catch any database query issues safely
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Import the server configuration layout
require_once("../config/db.php");

$message = "";

// Ensure database link exists gracefully
if (!isset($conn) || !$conn) {
    $message = "Database connection context is missing! Verify config/db.php";
}

/* =========================
   ADMIN SIGNUP
========================= */
if(isset($_POST['signup']) && $conn){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $check = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");

    if(mysqli_num_rows($check) > 0){
        $message = "Username already exists!";
    }else{
        /* HASH PASSWORD */
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn, "INSERT INTO admin(username,password) VALUES('$username','$hashed_password')");

        if($insert){
            $message = "Signup Successful! Please Login.";
        }else{
            $message = "Signup Failed: " . mysqli_error($conn);
        }
    }
}

/* =========================
   ADMIN LOGIN
========================= */
if(isset($_POST['login']) && $conn){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");

    if(mysqli_num_rows($query) > 0){
        $admin = mysqli_fetch_assoc($query);

        /* VERIFY HASHED PASSWORD */
        if(password_verify($password, $admin['password'])){
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: ./dashboard.php");
            exit();
        }else{
            $message = "Invalid Password!";
        }
    }else{
        $message = "Username Not Found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container{ width:400px; margin:80px auto; }
        .toggle-btns{ display:flex; gap:10px; margin-bottom:20px; }
        .toggle-btns button{ width:50%; }
        .form-box{ display:none; }
        .active{ display:block; }
        h2{ text-align:center; margin-bottom:20px; }
        .message{ background:white; padding:10px; border-radius:10px; margin-bottom:15px; text-align:center; font-weight:bold; color: #d9534f; }
    </style>
</head>
<body>
<div class="container">
    <?php if(!empty($message)){ ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <div class="toggle-btns">
        <button type="button" onclick="showLogin()">LOGIN</button>
        <button type="button" onclick="showSignup()">SIGNUP</button>
    </div>

    <!-- LOGIN FORM -->
    <form method="POST" autocomplete="off" id="loginForm" class="form-box active">
        <h2>Admin Login</h2>
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">LOGIN</button>
    </form>

    <!-- SIGNUP FORM -->
    <form method="POST" autocomplete="off" id="signupForm" class="form-box">
        <h2>Admin Signup</h2>
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="signup">SIGNUP</button>
    </form>
</div>
<script>
function showLogin(){
    document.getElementById("loginForm").classList.add("active");
    document.getElementById("signupForm").classList.remove("active");
}
function showSignup(){
    document.getElementById("signupForm").classList.add("active");
    document.getElementById("loginForm").classList.remove("active");
}
</script>
</body>
</html>
