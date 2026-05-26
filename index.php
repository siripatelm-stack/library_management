<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check if the user is trying to navigate using a fallback parameter
if (isset($_GET['goto'])) {
    $target = $_GET['goto'];
    if ($target === 'admin' && file_exists(__DIR__ . '/admin/admin_login.php')) {
        header("Location: /admin/admin_login.php");
        exit();
    } elseif ($target === 'student' && file_exists(__DIR__ . '/student/login.php')) {
        header("Location: /student/login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnosis Portal</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; background: #f4f6f9; color: #333; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { color: #111; font-size: 22px; }
        .btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; margin-right: 10px; }
        .btn-admin { background: #007bff; }
        .btn-student { background: #28a745; }
        .log-box { background: #222; color: #7cfc00; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 13px; overflow-x: auto; margin-top: 20px; }
    </style>
</head>
<body>

<div class="card">
    <h1>Library Management System Portal</h1>
    <p>Try these absolute routing pathways to bypass pathing restrictions:</p>
    
    <!-- Test Absolute URL Paths -->
    <a class="btn btn-admin" href="/admin/admin_login.php">Absolute Admin Link</a>
    <a class="btn btn-student" href="/student/login.php">Absolute Student Link</a>
    
    <!-- Fallback Query Paths -->
    <p style="margin-top:20px;">If the buttons above fail, try these internal query fallbacks:</p>
    <a href="?goto=admin" style="color: #007bff; font-weight:bold; margin-right:15px;">Fallback Admin</a>
    <a href="?goto=student" style="color: #28a745; font-weight:bold;">Fallback Student</a>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <h3>Server Directory Diagnostics</h3>
    <p>This box displays exactly what folders and files the live server can see in real-time:</p>
    <div class="log-box">
        <?php
        echo "<b>Current Directory Root:</b> " . __DIR__ . "<br><br>";
        
        $items = scandir(__DIR__);
        echo "<b>Found Top-Level Contents:</b><br>";
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $is_dir = is_dir(__DIR__ . '/' . $item) ? "[DIR] " : "[FILE] ";
                echo htmlspecialchars($is_dir . $item) . "<br>";
                
                // If it's a directory we are looking for, look inside it
                if (in_array(strtolower($item), ['admin', 'student'])) {
                    $sub_items = scandir(__DIR__ . '/' . $item);
                    foreach ($sub_items as $sub) {
                        if ($sub != '.' && $sub != '..') {
                            echo htmlspecialchars("&nbsp;&nbsp;&nbsp;&nbsp;└── " . $sub) . "<br>";
                        }
                    }
                }
            }
        }
        ?>
    </div>
</div>

</body>
</html>
