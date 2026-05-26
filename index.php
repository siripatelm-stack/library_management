<?php
// Start a secure session and signal to the server that this is a PHP engine file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management Portal</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background-color: #f0f2f5; 
            margin: 0; 
        }
        .portal-container { 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); 
            text-align: center; 
            max-width: 400px;
            width: 90%;
        }
        h1 { 
            color: #1a1a1a; 
            margin-bottom: 10px; 
            font-size: 24px;
        }
        p {
            color: #666666;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .btn { 
            display: block; 
            padding: 14px 20px; 
            margin: 12px 0; 
            color: #ffffff; 
            text-decoration: none; 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 16px;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn-admin { 
            background-color: #007bff; 
        }
        .btn-admin:hover {
            background-color: #0056b3;
        }
        .btn-student { 
            background-color: #28a745; 
        }
        .btn-student:hover {
            background-color: #1e7e34;
        }
        .btn:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <div class="portal-container">
        <h1>Library Management System</h1>
        <p>Select a portal below to sign in to your workspace.</p>
        
        <!-- Action Buttons linking directly to your project subdirectories -->
        <a class="btn btn-admin" href="./admin/admin_login.php">Admin Login Panel</a>
        <a class="btn btn-student" href="./student/login.php">Student Login Panel</a>
    </div>

</body>
</html>
