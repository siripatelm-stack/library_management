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

if(isset($_POST['update_profile'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];

    mysqli_query($conn,
    "UPDATE admin SET
    full_name='$full_name',
    email='$email',
    mobile='$mobile'
    WHERE username='$username'");

    header("Location: view_profile.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    background: linear-gradient(135deg,#667eea,#764ba2);
    font-family: Arial, sans-serif;
}

.edit-container{
    padding: 30px;
}

.edit-card{

    background: rgba(255,255,255,0.95);

    border-radius: 25px;

    padding: 30px;

    display: grid;

    grid-template-columns: 320px 1fr;

    gap: 30px;

    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.left-edit{

    background: white;

    border-radius: 20px;

    padding: 30px;

    text-align: center;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.left-edit img{

    width: 140px;
    height: 140px;

    border-radius: 50%;

    object-fit: cover;

    border: 6px solid #6c63ff;
}

.left-edit h3{
    margin-top: 20px;
}

.upload-btn{

    margin-top: 20px;

    background: #6c63ff;

    color: white;

    padding: 12px 20px;

    border-radius: 10px;

    display: inline-block;
}

.right-edit{

    background: white;

    border-radius: 20px;

    padding: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.right-edit h2{
    color: #6c63ff;
    margin-bottom: 25px;
}

.form-grid{

    display: grid;

    grid-template-columns: repeat(2,1fr);

    gap: 20px;
}

.form-group{
    display: flex;
    flex-direction: column;
}

.form-group label{
    margin-bottom: 10px;
    font-weight: bold;
}

.form-group input,
.form-group textarea{

    padding: 14px;

    border-radius: 10px;

    border: 1px solid #ccc;

    font-size: 15px;
}

.full-width{
    grid-column: span 2;
}

.save-btn{

    margin-top: 25px;

    background: #6c63ff;

    color: white;

    border: none;

    padding: 14px 24px;

    border-radius: 10px;

    font-size: 16px;

    cursor: pointer;
}

.back-btn{

    text-decoration: none;

    background: #555;

    color: white;

    padding: 14px 24px;

    border-radius: 10px;

    margin-left: 10px;
}

</style>

</head>

<body>

<div class="edit-container">

<div class="edit-card">

<!-- LEFT -->

<div class="left-edit">

<img src="uploads/default.png">

<h3>
<?php echo $admin['full_name']; ?>
</h3>

<div class="upload-btn">
<i class="fa fa-camera"></i>
Profile Photo
</div>

</div>

<!-- RIGHT -->

<div class="right-edit">

<h2>
<i class="fa fa-user-edit"></i>
Edit Profile
</h2>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>Full Name</label>

<input type="text"
name="full_name"

value="<?php echo $admin['full_name']; ?>"

required>

</div>

<div class="form-group">

<label>Username</label>

<input type="text"

value="<?php echo $admin['username']; ?>"

readonly>

</div>

<div class="form-group">

<label>Email</label>

<input type="email"
name="email"

value="<?php echo $admin['email']; ?>"

required>

</div>

<div class="form-group">

<label>Mobile</label>

<input type="text"
name="mobile"

value="<?php echo $admin['mobile']; ?>">

</div>

</div>

<button
type="submit"
name="update_profile"
class="save-btn">

<i class="fa fa-save"></i>
Update Profile

</button>

<a href="view_profile.php"
class="back-btn">

Back

</a>

</form>

</div>

</div>

</div>

</body>
</html>