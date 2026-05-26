<?php
session_start();

// Capture scanner mode and record ID if coming from the return button
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'issue';
$record_id = isset($_GET['id']) ? $_GET['id'] : '';


if(!isset($_SESSION['admin_username'])){

    header("Location: ./admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Issue Scanner</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; background: #f4f4f4; }
        .container { max-width: 700px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        #reader { width: 100%; max-width: 500px; margin: 20px auto; border: 1px solid #ddd; background: #000; }
        .btn { margin: 8px; padding: 12px 22px; background: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #1f2d3a; }
        .stop-btn { background: #c0392b; }
        #result { margin-top: 20px; font-size: 22px; font-weight: bold; color: #27ae60; }
        #status { margin-top: 10px; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <h1>Scan Book Barcode</h1>

    <button class="btn" onclick="startScanner()">Start Camera</button>
    <button class="btn stop-btn" onclick="stopScanner()">Stop Camera</button>

    <div id="reader"></div>

    <div id="status">Click "Start Camera" and scan a book barcode</div>
    <div id="result">ISBN will appear here</div>
</div>

<script>
    let html5QrCode = null;

    async function startScanner() {
        const statusMsg = document.getElementById("status");
        const resultMsg = document.getElementById("result");
        
        if (html5QrCode) { await stopScanner(); }

        html5QrCode = new Html5Qrcode("reader");

        const config = {
            fps: 15,
            qrbox: { width: 300, height: 150 }
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
        async (decodedText) => {

    resultMsg.innerText =
    "Scanned ISBN: " + decodedText;

    await stopScanner();

    setTimeout(() => {
    // Read the mode passed from PHP
    const mode = "<?php echo $mode; ?>";
    const recordId = "<?php echo $record_id; ?>";

    if (mode === "return") {
        // Redirect back to issued books file to run the status update and increment book copies
        window.location.href = 
        "issued_books.php?action=mark_returned&isbn=" + 
        encodeURIComponent(decodedText) + "&id=" + recordId;
    } else {
        // Default behavior for standard issuing/adding
        window.location.href =
        "add_book.php?scanned_isbn=" +
        encodeURIComponent(decodedText);
    }
}, 500);


}    
        ).catch(err => {
            statusMsg.innerText = "Error: " + err;
        });
    }

    async function stopScanner() {
        if (html5QrCode) {
            try {
                await html5QrCode.stop();
                html5QrCode = null;
                document.getElementById("status").innerText = "Camera stopped";
            } catch (err) { console.log(err); }
        }
    }
</script>
</body>
</html>