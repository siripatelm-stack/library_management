<?php
session_start();
if(!isset($_SESSION['admin_username'])){
header("Location: ./admin_login.php");
exit();
}
require_once("../config/db.php");
/*TOTAL BOOKS*/
$total_books=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT SUM(quantity) as total
FROM books
")
)['total'];
/*ISSUED BOOKS*/
$issued_books=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as issued
FROM issued_books
WHERE status='issued'
")
)['issued'];
/*AVAILABLE BOOKS*/

$available_books=max(0,$total_books-$issued_books);

/*OVERDUE BOOKS*/

$overdue_books=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as overdue
FROM issued_books
WHERE CURDATE() > DATE_ADD(issue_datetime, INTERVAL 2 DAY)
AND status='issued'
")
)['overdue'];

/*RETURNED BOOKS*/

$returned_books=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as returned
FROM issued_books
WHERE status='returned'
")
)['returned'];

/*TOP BOOKS*/

$top_books=mysqli_query($conn,"
SELECT book_name,COUNT(*) as total_issued
FROM issued_books
GROUP BY book_name
ORDER BY total_issued DESC
LIMIT 5
");

$book_names=[];
$issued_count=[];

while($row=mysqli_fetch_assoc($top_books)){

$book_names[]=$row['book_name'];
$issued_count[]=$row['total_issued'];

}

/*TOTAL STATUS*/

$total_status=$issued_books+$returned_books+$overdue_books;

/*ACTIVE STUDENTS*/

$active_student=mysqli_query($conn,"
SELECT student_name,COUNT(*) as total
FROM issued_books
GROUP BY student_name
ORDER BY total DESC
LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Dashboard</title>

<link rel="stylesheet" href="style.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

.chatbot-container{
position:fixed;
bottom:20px;
right:20px;
z-index:9999;
font-family:sans-serif;
}

.chatbot-toggle{
width:65px;
height:65px;
border-radius:50%;
background:#4f46e5;
color:white;
border:none;
cursor:pointer;
font-size:28px;
box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

.chatbot-panel{
position:absolute;
bottom:80px;
right:0;
width:350px;
height:500px;
background:white;
border-radius:20px;
display:none;
flex-direction:column;
overflow:hidden;
box-shadow:0 5px 20px rgba(0,0,0,0.2);
}

.chatbot-panel.open{
display:flex;
}

.chatbot-header{
background:#4f46e5;
color:white;
padding:15px;
display:flex;
justify-content:space-between;
align-items:center;
font-size:18px;
font-weight:bold;
}

.chatbot-messages{
flex:1;
padding:15px;
overflow-y:auto;
background:#f5f7fb;
display:flex;
flex-direction:column;
gap:10px;
}

.message{
max-width:85%;
padding:10px 15px;
border-radius:15px;
font-size:14px;
line-height:1.5;
}

.user-message{
align-self:flex-end;
background:#4f46e5;
color:white;
}

.bot-message{
align-self:flex-start;
background:white;
border:1px solid #ddd;
}

.chatbot-input{
display:flex;
padding:10px;
background:white;
border-top:1px solid #ddd;
}

.chatbot-input input{
flex:1;
padding:10px;
border:1px solid #ddd;
border-radius:20px;
outline:none;
}

.chatbot-input button{
width:40px;
height:40px;
border:none;
border-radius:50%;
background:#4f46e5;
color:white;
margin-left:8px;
cursor:pointer;
}

</style>

</head>

<body>

<div class="dashboard">

<?php require_once("sidebar.php"); ?>

<div class="main">

<div class="header">

<h2>
<i class="fa-solid fa-chart-line"></i>
Dashboard
</h2>

<div class="admin-profile">

<button type="button" class="profile-btn" onclick="toggleMenu()">

<i class="fa-solid fa-circle-user"></i>

ADMIN

<i class="fa-solid fa-caret-down"></i>

</button>

<div class="profile-dropdown" id="profileDropdown">

<a href="./view_profile.php">

<i class="fa-solid fa-user"></i>

View Profile

</a>

<a href="./edit_profile.php">

<i class="fa-solid fa-pen-to-square"></i>

Edit Profile

</a>

</div>

</div>

</div>

<div class="cards">

<div class="card">

<h3>Total Books</h3>

<p><?php echo $total_books; ?></p>

</div>

<div class="card">

<h3>Available Books</h3>

<p><?php echo $available_books; ?></p>

</div>

<div class="card">

<h3>Issued Books</h3>

<p><?php echo $issued_books; ?></p>

</div>

<div class="card">

<h3>Overdue Books</h3>

<p><?php echo $overdue_books; ?></p>

</div>
</div>

<!-- ANALYTICS SECTION -->
<div style="margin-top:28px;">

  <!-- Charts Row -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- Pie Chart: Top 3 Books -->
    <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
      <h3 style="font-size:18px;font-weight:700;color:#2c3e50;margin-bottom:16px;">
        <i class="fa-solid fa-book-open" style="color:#667eea;margin-right:8px;"></i>
        Top 3 Most Issued Books
      </h3>
      <div style="position:relative;height:220px;display:flex;align-items:center;justify-content:center;">
        <canvas id="topBooksChart"></canvas>
      </div>
    </div>

    <!-- Donut Chart: Issue Status -->
    <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
      <h3 style="font-size:18px;font-weight:700;color:#2c3e50;margin-bottom:16px;">
        <i class="fa-solid fa-chart-pie" style="color:#764ba2;margin-right:8px;"></i>
        Book Status Overview
      </h3>
      <div style="position:relative;height:220px;display:flex;align-items:center;justify-content:center;">
        <canvas id="statusChart"></canvas>
      </div>
    </div>

  </div>

  <!-- Most Active Students -->
  <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
    <h3 style="font-size:18px;font-weight:700;color:#2c3e50;margin-bottom:18px;">
      <i class="fa-solid fa-users" style="color:#667eea;margin-right:8px;"></i>
      Most Active Students
    </h3>
    <div id="studentsContainer">
      <p style="color:#999;text-align:center;padding:20px;">Loading...</p>
    </div>
  </div>

</div>
<!---chatbot--->
<button class="chatbot-toggle" onclick="toggleChatbot()">💬</button>
<div class="chatbot-popup" id="chatbotPopup">
    <div class="chatbot-header">
        🤖 Library AI Assistant
        <span onclick="toggleChatbot()" style="cursor:pointer;float:right;">✕</span>
    </div>
    <div class="chatbot-body" id="chatBody">
        <div class="bot-message">Hello Admin 👋<br>How can I help you today?</div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="userInput" placeholder="Type message..."
               onkeydown="if(event.key==='Enter') sendMessage()">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>
<script>

function toggleMenu(){

document.getElementById("profileDropdown").classList.toggle("show");

}

window.onclick=function(e){

if(!e.target.closest('.admin-profile')){

document.getElementById("profileDropdown").classList.remove("show");

}

}
let topBooksChart = null;
let statusChart = null;

async function loadAnalytics() {
  try {
    const res = await fetch("http://localhost:5001/analytics");
    const data = await res.json();

    // ---- PIE CHART: Top Books ----
    const bookColors = ["#667eea", "#764ba2", "#f093fb"];
    const bookCtx = document.getElementById("topBooksChart").getContext("2d");

    if (topBooksChart) topBooksChart.destroy();
    topBooksChart = new Chart(bookCtx, {
      type: "pie",
      data: {
        labels: data.top_books.map(b => b.book_name),
        datasets: [{
          data: data.top_books.map(b => b.total_issued),
          backgroundColor: bookColors,
          borderWidth: 3,
          borderColor: "#fff",
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: { font: { size: 12 }, padding: 14, usePointStyle: true }
          },
          tooltip: {
            callbacks: {
              label: ctx => ` ${ctx.label}: ${ctx.raw} times issued`
            }
          }
        }
      }
    });

    // ---- DONUT CHART: Status ----
    const s = data.status;
    const statusCtx = document.getElementById("statusChart").getContext("2d");

    if (statusChart) statusChart.destroy();
    statusChart = new Chart(statusCtx, {
      type: "doughnut",
      data: {
        labels: ["Issued", "Returned", "Overdue"],
        datasets: [{
          data: [s.issued, s.returned, s.overdue],
          backgroundColor: ["#3498db", "#27ae60", "#e74c3c"],
          borderWidth: 3,
          borderColor: "#fff",
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "65%",
        plugins: {
          legend: {
            position: "bottom",
            labels: { font: { size: 12 }, padding: 14, usePointStyle: true }
          },
          tooltip: {
            callbacks: {
              label: ctx => ` ${ctx.label}: ${ctx.raw} books`
            }
          }
        }
      }
    });

    // ---- ACTIVE STUDENTS TABLE ----
    const container = document.getElementById("studentsContainer");
    if (!data.active_students.length) {
      container.innerHTML = '<p style="color:#999;text-align:center;">No data found.</p>';
      return;
    }

    const maxTotal = data.active_students[0].total;
    const medals = ["🥇", "🥈", "🥉"];
    const barColors = ["#667eea", "#764ba2", "#f093fb", "#4facfe", "#43e97b"];

    container.innerHTML = data.active_students.map((s, i) => `
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <span style="font-size:20px;width:30px;text-align:center;">
          ${i < 3 ? medals[i] : "👤"}
        </span>
        <div style="flex:1;">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
            <span style="font-weight:600;font-size:14px;color:#2c3e50;">${s.student_name}</span>
            <span style="font-size:13px;color:#667eea;font-weight:600;">${s.total} books</span>
          </div>
          <div style="background:#f0f0f8;border-radius:20px;height:10px;overflow:hidden;">
            <div style="
              width:${Math.round((s.total / maxTotal) * 100)}%;
              height:100%;
              background:${barColors[i] || '#667eea'};
              border-radius:20px;
              transition:width 1s ease;
            "></div>
          </div>
        </div>
      </div>
    `).join("");

  } catch (err) {
    console.error("Analytics error:", err);
    document.getElementById("studentsContainer").innerHTML =
      '<p style="color:#e74c3c;text-align:center;">⚠️ Cannot connect to analytics server. Make sure analytics.py is running on port 5001.</p>';
  }
}

loadAnalytics();
//chatbot script
function toggleChatbot() {
    document.getElementById("chatbotPopup").classList.toggle("show-chatbot");
}

async function sendMessage() {
    let input = document.getElementById("userInput");
    let message = input.value.trim();
    if (message === "") return;

    let chatBody = document.getElementById("chatBody");
    chatBody.innerHTML += `<div class="user-message">${message}</div>`;
    input.value = "";
    chatBody.innerHTML += `<div class="bot-message" id="typing">Typing...</div>`;
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
    let res = await fetch("http://127.0.0.1:5003/chat", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: message })
    });

    console.log("Status:", res.status);  // check response code

    let data = await res.json();
    console.log("Response:", data);      // check what Flask returns

    document.getElementById("typing").remove();
    chatBody.innerHTML += `<div class="bot-message">${data.reply}</div>`;

} catch(e) {
    console.log("FETCH ERROR:", e);      // see the real error
    document.getElementById("typing").remove();
    chatBody.innerHTML += `<div class="bot-message">⚠️ Error: ${e.message}</div>`;
}
    chatBody.scrollTop = chatBody.scrollHeight;
}


</script>
</body>
</html>