<?php

session_start();

if(!isset($_SESSION['admin_username'])){
    header("Location: admin_login.php");
    exit();
}

require_once("../config/db.php");

$username = $_SESSION['admin_username'];

$query = mysqli_query($conn,
"SELECT * FROM admin WHERE username='$username'");

$admin = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Profile</title>

<link rel="stylesheet" href="style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    background: linear-gradient(135deg,#667eea,#764ba2);
    font-family: Arial, sans-serif;
}

.profile-container{
    padding: 30px;
}

.profile-card{
    background: rgba(255,255,255,0.95);

    border-radius: 25px;

    padding: 30px;

    display: grid;

    grid-template-columns: 320px 1fr;

    gap: 30px;

    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.left-profile{

    background: white;

    border-radius: 20px;

    padding: 30px;

    text-align: center;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.left-profile img{
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;

    border: 6px solid #6c63ff;
}

.left-profile h2{
    margin-top: 20px;
    color: #222;
}

.role-badge{
    background: #6c63ff;
    color: white;

    padding: 8px 16px;

    border-radius: 20px;

    display: inline-block;

    margin-top: 10px;
}

.profile-info{
    margin-top: 25px;
    text-align: left;
}

.profile-info p{
    margin: 15px 0;
    color: #555;
}

.right-profile{
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.info-box{
    background: white;

    padding: 25px;

    border-radius: 20px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.info-box h3{
    color: #6c63ff;
    margin-bottom: 20px;
}

.info-grid{
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 20px;
}

.info-item label{
    font-weight: bold;
    color: #444;
}

.info-item p{
    margin-top: 8px;
    color: #666;
}

.action-buttons{
    margin-top: 20px;
}

.action-buttons a{
    text-decoration: none;

    padding: 12px 22px;

    border-radius: 10px;

    margin-right: 10px;

    color: white;

    background: #6c63ff;
}

</style>

</head>

<body>

<div class="profile-container">

<div class="profile-card">

<!-- LEFT -->

<div class="left-profile">

<img src="uploads/default.png">

<h2>
<?php echo $admin['full_name']; ?>
</h2>

<div class="role-badge">
<?php echo $admin['role']; ?>
</div>

<div class="profile-info">

<p>
<i class="fa fa-envelope"></i>
<?php echo $admin['email']; ?>
</p>

<p>
<i class="fa fa-phone"></i>
<?php echo $admin['mobile']; ?>
</p>

<p>
<i class="fa fa-calendar"></i>
Joined:
<?php echo date("d M Y",
strtotime($admin['created_at'])); ?>
</p>

</div>

</div>

<!-- RIGHT -->

<div class="right-profile">

<div class="info-box">

<h3>
<i class="fa fa-user"></i>
Personal Information
</h3>

<div class="info-grid">

<div class="info-item">
<label>Full Name</label>
<p><?php echo $admin['full_name']; ?></p>
</div>

<div class="info-item">
<label>Username</label>
<p><?php echo $admin['username']; ?></p>
</div>

<div class="info-item">
<label>Email</label>
<p><?php echo $admin['email']; ?></p>
</div>

<div class="info-item">
<label>Mobile</label>
<p><?php echo $admin['mobile']; ?></p>
</div>

<div class="info-item">
<label>Role</label>
<p><?php echo $admin['role']; ?></p>
</div>

</div>

</div>

<div class="action-buttons">

<a href="dashboard.php">
<i class="fa fa-arrow-left"></i>
Back Dashboard
</a>

</div>

</div>

</div>

</div>

</body>
</html>