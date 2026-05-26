<?php
require_once("../config/db.php");
session_start();
if(!isset($_SESSION['student_usn'])){
    header("Location: login.php");
    exit();
}
$message = "";
$book = null;

/* ISSUE BOOK */
if(isset($_POST['issue_book'])){
    $isbn = $_POST['isbn'];
    $student_name = $_SESSION['student_name'];
    $student_usn = $_SESSION['student_usn'];
    
    $get_book = mysqli_query($conn, "SELECT * FROM books WHERE isbn='$isbn'");
    if(mysqli_num_rows($get_book) > 0){
        $book_data = mysqli_fetch_assoc($get_book);
        if($book_data['available_quantity'] <= 0){
            $message = "<div style='color:red; font-weight:bold; margin-bottom:15px;'>Book Out Of Stock!</div>";
        }else{
            $book_name = $book_data['book_name'];
            $issue_datetime = date("Y-m-d H:i:s");
            $return_due_date = date("Y-m-d", strtotime("+14 days"));
            $status = "issued";
            
            mysqli_query($conn,"INSERT INTO issued_books (student_name, usn, book_name, isbn, issue_datetime, return_due_date, status) VALUES ('$student_name', '$student_usn', '$book_name', '$isbn', '$issue_datetime', '$return_due_date', '$status')");
            
            $new_available = $book_data['available_quantity'] - 1;
            mysqli_query($conn,"UPDATE books SET available_quantity='$new_available' WHERE isbn='$isbn'");

            if($new_available > 2){
                $book_status = "Available";
            } elseif($new_available > 0){
                $book_status = "Low Stock";
            } else {
                $book_status = "Out of Stock";
            }

            mysqli_query($conn,"UPDATE books SET status='$book_status' WHERE isbn='$isbn'");

            header("Location: scanner.php?success=1");
            exit();
        }
    }else{
        $message = "<div style='color:red; font-weight:bold; margin-bottom:15px;'>Book not found!</div>";
    }
}

/* SUCCESS MESSAGE */
if(isset($_GET['success'])){
    $message = "<div style='color:green; font-weight:bold; margin-bottom:15px;'>Book issued successfully!</div>";
}

/* SCANNED ISBN */
if(isset($_GET['scanned_isbn'])){
    $scanned_isbn = mysqli_real_escape_string($conn, $_GET['scanned_isbn']);
    $get_book = mysqli_query($conn, "SELECT * FROM books WHERE isbn='$scanned_isbn'");

    if(mysqli_num_rows($get_book) > 0){
        $book = mysqli_fetch_assoc($get_book);
    }else{
        $message = "<div style='color:red; font-weight:bold; margin-bottom:15px;'>Book not found!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Scanner</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <style>
        body{ font-family:Arial,sans-serif; background:#f4f4f4; padding:20px; text-align:center; }
        .container{ max-width:700px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
        #reader{ width:100%; max-width:500px; margin:20px auto; background:#222; border-radius:8px; overflow:hidden; }
        .btn{ margin:8px; padding:12px 22px; background:#2c3e50; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
        .stop-btn{ background:#c0392b; }
        .issue-btn{ background:#2563eb; padding:12px 20px; border:none; border-radius:8px; color:white; cursor:pointer; font-weight:bold; }
        #status{ margin-top:10px; color:#555; font-weight:500; }
        #result{ margin-top:20px; font-size:22px; font-weight:bold; color:green; }
    </style>
</head>
<body>

<div class="container">
    <h1>📷 Scan Book Barcode</h1>
    <?php echo $message; ?>

    <button class="btn" onclick="startScanner()">Start Camera</button>
    <button class="btn stop-btn" onclick="stopScanner()">Stop Camera</button>

    <div id="reader"></div>
    <div id="status">Click Start Camera</div>
    <div id="result">ISBN will appear here</div>

    <?php if($book){ ?>
    <div style="margin-top:20px; padding:20px; background:#f0fdf4; border-radius:10px; border:1px solid #bbf7d0;">
        <h3>📘 Book Found</h3>
        <p><b>Book Name:</b> <?php echo htmlspecialchars($book['book_name']); ?></p>
        <p><b>ISBN:</b> <?php echo htmlspecialchars($book['isbn']); ?></p>
        <p><b>Category:</b> <?php echo htmlspecialchars($book['category']); ?></p>
        <p><b>Available:</b> <?php echo htmlspecialchars($book['available_quantity']); ?></p>

        <form method="POST">
            <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
            <button type="submit" name="issue_book" class="issue-btn">Issue Book</button>
        </form>
    </div>
    <?php } ?>
</div>

<script>
let html5QrCode = null;

async function startScanner(){
    const statusMsg = document.getElementById("status");
    const resultMsg = document.getElementById("result");

    statusMsg.innerText = "Requesting camera permissions...";

    try {
        // Request the camera track to trigger the browser prompt
        const testStream = await navigator.mediaDevices.getUserMedia({ video: true });
        
        // CRUCIAL FIX: Stop the test tracks immediately to unlock the camera hardware resource!
        testStream.getTracks().forEach(track => track.stop());
    } catch(err) {
        statusMsg.innerText = "Camera Permission Denied or Device Blocked.";
        return;
    }

    if (typeof Html5Qrcode === "undefined") {
        statusMsg.innerText = "Scanner library failed to load. Check CDN connection.";
        return;
    }

    if (html5QrCode) {
        await stopScanner();
    }

    try {
        html5QrCode = new Html5Qrcode("reader");
    } catch(err) {
        statusMsg.innerText = "Scanner Initialization Error: " + err;
        return;
    }

    // Set configuration boxes optimization metrics
    const config = {
        fps: 10,
        qrbox: { width: 280, height: 160 }
    };

    statusMsg.innerText = "Opening rear camera lens...";

    html5QrCode.start(
        { facingMode: "environment" }, // Focuses directly on back scanner camera lens
        config,
        async (decodedText) => {
            resultMsg.innerText = "Scanned Data: " + decodedText;
            await stopScanner();

            // Send scanner output directly to URL mapping parameter routing
            setTimeout(() => {
                window.location.href = "scanner.php?scanned_isbn=" + encodeURIComponent(decodedText);
            }, 300);
        }
    ).then(() => {
        statusMsg.innerText = "Camera active. Center the barcode inside the target box.";
    }).catch(err => {
        console.error(err);
        statusMsg.innerText = "Hardware Error: " + err + ". Ensure background apps are closed and link uses HTTPS.";
    });
}

async function stopScanner(){
    if(html5QrCode){
        try {
            await html5QrCode.stop();
            html5QrCode = null;
            document.getElementById("reader").innerHTML = "";
            document.getElementById("status").innerText = "Camera stopped successfully.";
        } catch(err) {
            console.log("Stop logging capture details: ", err);
        }
    }
}
</script>

</body>
</html>
