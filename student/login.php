<?php
session_start();

require_once("../config/db.php");

$message = null;

/* SIGNUP */

if(isset($_POST['signup'])){

   $student_name = $_POST['student_name'];

   $usn = strtoupper($_POST['usn']);

   $email = $_POST['email'];
    
   $phone = $_POST['phone'];

   $password = $_POST['password'];

   /* USN VALIDATION */

   if(!preg_match("/^4GW\d{2}CI\d{3}$/", $usn)){

      $message = "Invalid USN Format!";

   }

   /* CHECK EXISTING USER */

   if(empty($message)){

      $check = mysqli_query($conn,
      "SELECT * FROM students
      WHERE email='$email'
      OR student_usn='$usn'");

      if(mysqli_num_rows($check) > 0){

         $message = "Email or USN already exists!";

      }else{

         $hashed_password =
         password_hash($password, PASSWORD_DEFAULT);

         $insert = mysqli_query($conn,
         "INSERT INTO students
         (student_name,student_usn,email,phone,password)

         VALUES
         ('$student_name','$usn','$email','$phone','$hashed_password')");

         if($insert){

            $message = "Signup Successful!";

         }else{

            $message = "Signup Failed!";
         }
      }
   }
}

/* LOGIN */

if(isset($_POST['login'])){

   $usn = strtoupper(trim($_POST['usn']));

   $password = $_POST['password'];

   $query = mysqli_query($conn,
   "SELECT * FROM students
   WHERE student_usn='$usn'");

   if(mysqli_num_rows($query) > 0){

      $student = mysqli_fetch_assoc($query);

      if(password_verify($password, $student['password'])){

         $_SESSION['student_name'] =
         $student['student_name'];

         $_SESSION['student_usn'] =
         $student['student_usn'];

         $_SESSION['student_phone'] =
         $student['phone'];
            $_SESSION['student_email'] =
         $student['email']; 
         header("Location: student_dashboard.php");
         exit();

      }else{

         $message = "Invalid Password!";
      }

   }else{

      $message = "USN Not Found!";
   }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Student Login</title>

<link rel="stylesheet" href="student.css">

<style>

.container{
width:400px;
margin:80px auto;
}

.toggle-btns{
display:flex;
gap:10px;
margin-bottom:20px;
}

.toggle-btns button{
width:50%;
}

.form-box{
display:none;
}

.active{
display:block;
}

h2{
text-align:center;
margin-bottom:20px;
}

.message{
background:white;
padding:10px;
border-radius:10px;
margin-bottom:15px;
text-align:center;
font-weight:bold;
}

</style>

</head>

<body>

<div class="container">

<?php if(!empty($message)){ ?>

<div class="message">
<?php echo $message; ?>
</div>

<?php } ?>

<div class="toggle-btns">

<button type="button" onclick="showLogin()">
LOGIN
</button>

<button type="button" onclick="showSignup()">
SIGNUP
</button>

</div>

<!-- LOGIN FORM -->

<form method="POST"
autocomplete="off"
id="loginForm"
class="form-box active">

<h2>Student Login</h2>

<input
type="text"
name="usn"
autocomplete="off"
placeholder="Enter USN"
required>

<input
type="password"
name="password"
autocomplete="new-password"
placeholder="Enter Password"
required>

<button type="submit" name="login">
LOGIN
</button>

</form>

<!-- SIGNUP FORM -->

<form method="POST"
autocomplete="off"
id="signupForm"
class="form-box">

<h2>Student Signup</h2>

<input
type="text"
name="student_name"
autocomplete="off"
placeholder="Enter Name"
required>

<input
type="text"
name="usn"
autocomplete="off"
placeholder="Enter USN"
required>

<input
type="text"
name="phone"
autocomplete="off"
placeholder="Enter Phone Number"
required>

<input
type="email"
name="email"
autocomplete="off"
placeholder="Enter Email"
required>

<input
type="password"
name="password"
autocomplete="new-password"
placeholder="Enter Password"
required>

<button type="submit" name="signup">
SIGNUP
</button>

</form>

</div>

<script>

function showLogin(){

document.getElementById("loginForm")
.classList.add("active");

document.getElementById("signupForm")
.classList.remove("active");
}

function showSignup(){

document.getElementById("signupForm")
.classList.add("active");

document.getElementById("loginForm")
.classList.remove("active");
}

</script>

</body>
</html>