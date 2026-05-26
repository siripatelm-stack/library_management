<?php
session_start();
if(!isset($_SESSION['student_usn'])){
    header("Location: ./login.php");
    exit();
}
require_once("../config/db.php");
$student_usn = $_SESSION['student_usn'] ?? 'N/A';

/* TOTAL BORROWED */
$total_borrowed = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total
    FROM issued_books
    WHERE usn='$student_usn'")
)['total'];

/* RETURNED */
$returned = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as returned
    FROM issued_books
    WHERE usn='$student_usn'
    AND status='returned'")
)['returned'];

/* PENDING */
$pending = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as pending
    FROM issued_books
    WHERE usn='$student_usn'
    AND status='issued'")
)['pending'];

/* OVERDUE BOOKS */
$overdue=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) as overdue
FROM issued_books
WHERE CURDATE() > DATE_ADD(issue_datetime, INTERVAL 2 DAY)
AND usn='$student_usn'
AND status='issued'
")
)['overdue'];

/* BOOKS */
$result = mysqli_query($conn,"SELECT book_name, issue_datetime, return_due_date, status
FROM issued_books
WHERE usn='$student_usn'
ORDER BY id DESC");

/* FETCH STUDENT DETAILS */
$stmt = $conn->prepare("SELECT * FROM students WHERE student_usn = ?");
$stmt->bind_param("s", $student_usn);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>
<link rel="stylesheet" href="./student.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30%           { transform: translateY(-6px); }
}
</style>
</head>
<body>
<div class="dashboard">
<?php require_once("student_sidebar.php"); ?>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <div class="header">
        <h2>🎓 Student Dashboard</h2>
        <p>Welcome, <?= htmlspecialchars($student['student_name']); ?> 👋</p>
    </div>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Total Borrowed</h3>
            <p><?php echo $total_borrowed; ?></p>
        </div>
        <div class="card">
            <h3>Books Returned</h3>
            <p><?php echo $returned; ?></p>
        </div>
        <div class="card">
            <h3>Pending Returns</h3>
            <p><?php echo $pending; ?></p>
        </div>
        <div class="card">
            <h3>Overdue Books</h3>
            <p><?php echo $overdue; ?></p>
        </div>
    </div>

    <!-- ANALYTICS SECTION -->
    <div style="margin-top:28px;">

        <!-- Charts Row -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;">

            <!-- Donut: Book Status -->
            <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:24px;
                        box-shadow:0 10px 40px rgba(0,0,0,0.1);">
                <h3 style="font-size:18px;font-weight:700;color:#2c3e50;margin-bottom:4px;">
                    📊 Book Status Breakdown
                </h3>
                <p style="font-size:13px;color:#7f8c8d;margin-bottom:16px;">
                    <strong>Your current book status</strong>
                </p>
                <div style="position:relative;height:230px;display:flex;
                            align-items:center;justify-content:center;">
                    <canvas id="statusDonut"></canvas>
                </div>
            </div>

            <!-- Bar: Monthly Activity -->
            <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:24px;
                        box-shadow:0 10px 40px rgba(0,0,0,0.1);">
                <h3 style="font-size:18px;font-weight:700;color:#2c3e50;margin-bottom:4px;">
                    📅 Monthly Reading Activity
                </h3>
                <p style="font-size:13px;color:#7f8c8d;margin-bottom:16px;">
                    <strong>Books borrowed by month</strong>
                </p>
                <div style="position:relative;height:230px;">
                    <canvas id="monthlyBar"></canvas>
                </div>
            </div>
        </div>

        <!-- RECOMMENDATIONS SECTION -->
        <div style="margin-top:22px;">
            <div style="background:rgba(255,255,255,0.95);border-radius:20px;padding:28px;
                        box-shadow:0 10px 40px rgba(0,0,0,0.1);position:relative;overflow:hidden;">

                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;
                            background:linear-gradient(135deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));
                            border-radius:50%;"></div>
                <div style="position:absolute;bottom:-20px;left:-20px;width:100px;height:100px;
                            background:linear-gradient(135deg,rgba(102,126,234,0.08),rgba(118,75,162,0.08));
                            border-radius:50%;"></div>

                <div style="position:relative;z-index:1;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                        <div style="width:42px;height:42px;background:linear-gradient(135deg,#667eea,#764ba2);
                                    border-radius:12px;display:flex;align-items:center;
                                    justify-content:center;font-size:20px;">✨</div>
                        <div>
                            <h3 style="font-size:20px;font-weight:700;color:#2c3e50;margin:0;">
                                Recommended For You
                            </h3>
                        </div>
                    </div>
                    <div id="recoContent" style="margin-top:20px;">
                        <div style="text-align:center;padding:20px;color:#999;">
                            <div style="font-size:30px;margin-bottom:8px;">⏳</div>
                            Loading recommendations...
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- end analytics -->

</div><!-- end main -->
</div><!-- end dashboard -->

<!-- ── FLOATING CHAT BUBBLE ───────────────────────────────────────────────── -->
<button id="chatToggleBtn" onclick="toggleChat()"
  style="position:fixed;bottom:28px;right:28px;z-index:9999;
         width:58px;height:58px;border-radius:50%;border:none;
         background:linear-gradient(135deg,#667eea,#764ba2);
         color:white;font-size:24px;cursor:pointer;
         box-shadow:0 8px 25px rgba(102,126,234,0.5);
         display:flex;align-items:center;justify-content:center;
         transition:all 0.3s;">
  💬
</button>

<!-- ── FLOATING CHATBOT PANEL ─────────────────────────────────────────────── -->
<div id="chatbotSection"
  style="display:none;position:fixed;bottom:100px;right:28px;z-index:9998;
         width:370px;background:white;border-radius:20px;
         box-shadow:0 20px 60px rgba(0,0,0,0.2);
         border:1px solid rgba(102,126,234,0.2);overflow:hidden;">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,#667eea,#764ba2);
                padding:16px 20px;display:flex;align-items:center;
                justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);
                        border-radius:50%;display:flex;align-items:center;
                        justify-content:center;font-size:18px;">🤖</div>
            <div>
                <p style="color:white;font-weight:700;font-size:15px;margin:0;">
                    Library Assistant
                </p>
                <p style="color:rgba(255,255,255,0.75);font-size:11px;margin:0;">
                    Ask about your books
                </p>
            </div>
        </div>
        <button onclick="toggleChat()"
          style="background:rgba(255,255,255,0.2);border:none;border-radius:50%;
                 width:30px;height:30px;color:white;font-size:16px;cursor:pointer;
                 display:flex;align-items:center;justify-content:center;">
            ✕
        </button>
    </div>

    <!-- Chat Box -->
    <div id="chatBox"
      style="height:280px;overflow-y:auto;padding:16px;background:#f8f9ff;
             display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:8px;align-items:flex-start;">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,#667eea,#764ba2);
                        border-radius:50%;display:flex;align-items:center;
                        justify-content:center;font-size:12px;flex-shrink:0;">🤖</div>
            <div style="background:white;padding:10px 13px;border-radius:0 12px 12px 12px;
                        font-size:13px;color:#2c3e50;max-width:85%;
                        box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                Hi <?= htmlspecialchars($student['student_name']); ?>! 👋
                Ask me about your books, due dates or overdue status.
            </div>
        </div>
    </div>

    <!-- Quick Suggestions -->
    <div style="padding:10px 14px;background:white;
                border-top:1px solid rgba(102,126,234,0.1);
                display:flex;flex-wrap:wrap;gap:6px;">
        <button onclick="sendQuick('What books do I currently have?')"
          style="background:#eef0fd;color:#667eea;border:1px solid rgba(102,126,234,0.3);
                 border-radius:20px;padding:5px 11px;font-size:11px;font-weight:600;cursor:pointer;">
            📚 My books
        </button>
        <button onclick="sendQuick('Do I have any overdue books?')"
          style="background:#fef5e7;color:#e67e22;border:1px solid rgba(230,126,34,0.3);
                 border-radius:20px;padding:5px 11px;font-size:11px;font-weight:600;cursor:pointer;">
            ⏰ Overdue?
        </button>
        <button onclick="sendQuick('When are my books due?')"
          style="background:#eafaf1;color:#27ae60;border:1px solid rgba(39,174,96,0.3);
                 border-radius:20px;padding:5px 11px;font-size:11px;font-weight:600;cursor:pointer;">
            📅 Due dates
        </button>
        <button onclick="sendQuick('How many books have I borrowed in total?')"
          style="background:#f3eeff;color:#764ba2;border:1px solid rgba(118,75,162,0.3);
                 border-radius:20px;padding:5px 11px;font-size:11px;font-weight:600;cursor:pointer;">
            📊 My stats
        </button>
    </div>

    <!-- Input -->
    <div style="padding:12px 14px;background:white;
                border-top:1px solid rgba(102,126,234,0.1);display:flex;gap:8px;">
        <input type="text" id="chatInput"
          placeholder="Type your question..."
          onkeydown="if(event.key==='Enter') sendChat()"
          style="flex:1;padding:10px 14px;border:2px solid rgba(102,126,234,0.2);
                 border-radius:12px;font-size:13px;outline:none;transition:border-color 0.3s;"
          onfocus="this.style.borderColor='#667eea'"
          onblur="this.style.borderColor='rgba(102,126,234,0.2)'"/>
        <button onclick="sendChat()"
          style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;
                 border:none;border-radius:12px;padding:10px 16px;
                 font-size:16px;cursor:pointer;">
            ➤
        </button>
    </div>

</div>

<!-- ── SCRIPTS ─────────────────────────────────────────────────────────────── -->
<script>
const STUDENT_USN  = "<?php echo $student_usn; ?>";
const STUDENT_NAME = "<?php echo htmlspecialchars($student['student_name']); ?>";

let donutChart = null, barChart = null;

// ── Analytics ────────────────────────────────────────────────────────────────

async function loadStudentAnalytics() {
    try {
        const res  = await fetch(`http://127.0.0.1:5002/student/analytics/${STUDENT_USN}`);
        const data = await res.json();
        const s    = data.summary;

        // Donut Chart
        const donutCtx = document.getElementById("statusDonut").getContext("2d");
        if (donutChart) donutChart.destroy();
        donutChart = new Chart(donutCtx, {
            type: "doughnut",
            data: {
                labels: ["Returned", "Pending", "Overdue"],
                datasets: [{
                    data: [s.returned, s.pending, s.overdue],
                    backgroundColor: ["#27ae60", "#f39c12", "#e74c3c"],
                    borderWidth: 3,
                    borderColor: "#fff",
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "68%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: { font:{size:12}, padding:14, usePointStyle:true }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} books`
                        }
                    }
                }
            }
        });

        // Bar Chart
        const months = data.monthly.map(m => m.month);
        const totals = data.monthly.map(m => m.total);
        const barCtx = document.getElementById("monthlyBar").getContext("2d");
        if (barChart) barChart.destroy();
        barChart = new Chart(barCtx, {
            type: "bar",
            data: {
                labels: months,
                datasets: [{
                    label: "Books Borrowed",
                    data: totals,
                    backgroundColor: months.map((_,i) =>
                        `hsla(${240 + i*15},70%,${60 - i*3}%,0.85)`
                    ),
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw} book${ctx.raw !== 1 ? 's' : ''} borrowed`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize:1, font:{size:12} },
                        grid: { color:"rgba(0,0,0,0.05)" }
                    },
                    x: {
                        ticks: { font:{size:12} },
                        grid: { display:false }
                    }
                }
            }
        });

    } catch (err) {
        console.error("Student analytics error:", err);
    }
}

// ── Recommendations ───────────────────────────────────────────────────────────

async function loadRecommendations() {
    try {
        const res  = await fetch(`http://127.0.0.1:5002/student/recommendations/${STUDENT_USN}`);
        const data = await res.json();
        const container = document.getElementById("recoContent");

        if (data.error) {
            container.innerHTML = `<p style="color:#e74c3c;">⚠️ ${data.error}</p>`;
            return;
        }

        const myBooksHtml = data.my_books.length ? `
            <div style="margin-bottom:22px;">
                <p style="font-size:13px;font-weight:600;color:#7f8c8d;
                          text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">
                    📚 Your Reading History
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    ${data.my_books.map(b => `
                        <span style="background:#eef0fd;color:#667eea;padding:6px 14px;
                                     border-radius:20px;font-size:13px;font-weight:500;
                                     border:1px solid rgba(102,126,234,0.2);">
                            ${b}
                        </span>
                    `).join("")}
                </div>
            </div>` : "";

        if (!data.recommendations.length) {
            container.innerHTML = myBooksHtml + `
                <div style="text-align:center;padding:30px;background:#f8f9ff;
                            border-radius:14px;border:2px dashed rgba(102,126,234,0.3);">
                    <div style="font-size:36px;margin-bottom:10px;">🔍</div>
                    <p style="color:#7f8c8d;font-size:15px;margin:0;">
                        No recommendations yet.<br>
                        <span style="font-size:13px;">Borrow more books to get personalized suggestions!</span>
                    </p>
                </div>`;
            return;
        }

        const icons  = ["🥇","🥈","🥉","📗","📘"];
        const colors = ["#667eea","#764ba2","#f093fb","#4facfe","#43e97b"];
        const bgcols = ["#eef0fd","#f3eeff","#fdf0ff","#e8f4ff","#eafff3"];

        container.innerHTML = myBooksHtml + `
            <p style="font-size:13px;font-weight:600;color:#7f8c8d;
                      text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
                ⭐ Books You Might Like
            </p>
            <div style="display:flex;flex-direction:column;gap:12px;">
                ${data.recommendations.map((r, i) => `
                    <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;
                                background:${bgcols[i]||"#f8f9ff"};border-radius:14px;
                                border-left:4px solid ${colors[i]||"#667eea"};
                                transition:transform 0.2s,box-shadow 0.2s;cursor:default;"
                         onmouseover="this.style.transform='translateX(6px)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)'"
                         onmouseout="this.style.transform='translateX(0)';this.style.boxShadow='none'">
                        <span style="font-size:22px;">${icons[i]||"📖"}</span>
                        <div style="flex:1;">
                            <p style="margin:0;font-weight:600;font-size:15px;color:#2c3e50;">
                                ${r.book_name}
                            </p>
                            <p style="margin:0;font-size:12px;color:#7f8c8d;margin-top:2px;">
                                ${r.score} student${r.score>1?"s":""} with similar taste also read this
                            </p>
                        </div>
                        <span style="background:${colors[i]||"#667eea"};color:white;
                                     padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                            #${i+1}
                        </span>
                    </div>
                `).join("")}
            </div>`;

    } catch (err) {
        document.getElementById("recoContent").innerHTML = `
            <p style="color:#e74c3c;text-align:center;">
                ⚠️ Cannot load recommendations. Make sure analytics.py is running on port 5002.
            </p>`;
    }
}

// ── Chatbot ───────────────────────────────────────────────────────────────────

function toggleChat() {
    const section  = document.getElementById("chatbotSection");
    const btn      = document.getElementById("chatToggleBtn");
    const isHidden = section.style.display === "none";
    section.style.display = isHidden ? "block" : "none";
    btn.textContent      = isHidden ? "✕" : "💬";
    btn.style.background = isHidden
        ? "linear-gradient(135deg,#e74c3c,#c0392b)"
        : "linear-gradient(135deg,#667eea,#764ba2)";
}

function appendMessage(role, text) {
    const chatBox = document.getElementById("chatBox");
    const isUser  = role === "user";

    const wrapper = document.createElement("div");
    wrapper.style.cssText = `
        display:flex;gap:8px;align-items:flex-start;
        justify-content:${isUser ? "flex-end" : "flex-start"};`;

    const avatar = document.createElement("div");
    avatar.style.cssText = `
        width:28px;height:28px;border-radius:50%;flex-shrink:0;
        display:flex;align-items:center;justify-content:center;font-size:12px;
        order:${isUser ? 2 : 0};
        background:${isUser
            ? "linear-gradient(135deg,#27ae60,#2ecc71)"
            : "linear-gradient(135deg,#667eea,#764ba2)"};`;
    avatar.textContent = isUser ? "👤" : "🤖";

    const bubble = document.createElement("div");
    bubble.style.cssText = `
        padding:10px 13px;font-size:13px;max-width:82%;
        line-height:1.6;white-space:pre-wrap;
        box-shadow:0 2px 8px rgba(0,0,0,0.06);
        background:${isUser
            ? "linear-gradient(135deg,#667eea,#764ba2)"
            : "white"};
        color:${isUser ? "white" : "#2c3e50"};
        border-radius:${isUser ? "12px 0 12px 12px" : "0 12px 12px 12px"};`;
    bubble.textContent = text;

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    chatBox.appendChild(wrapper);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTyping() {
    const chatBox = document.getElementById("chatBox");
    const div     = document.createElement("div");
    div.id        = "typingIndicator";
    div.style.cssText = "display:flex;gap:8px;align-items:flex-start;";
    div.innerHTML = `
        <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;
                    background:linear-gradient(135deg,#667eea,#764ba2);
                    display:flex;align-items:center;justify-content:center;font-size:12px;">🤖</div>
        <div style="background:white;padding:10px 14px;border-radius:0 12px 12px 12px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <span style="display:inline-flex;gap:4px;align-items:center;">
                <span style="width:6px;height:6px;border-radius:50%;background:#667eea;
                             display:inline-block;animation:typingBounce 1s infinite 0s;"></span>
                <span style="width:6px;height:6px;border-radius:50%;background:#667eea;
                             display:inline-block;animation:typingBounce 1s infinite 0.2s;"></span>
                <span style="width:6px;height:6px;border-radius:50%;background:#667eea;
                             display:inline-block;animation:typingBounce 1s infinite 0.4s;"></span>
            </span>
        </div>`;
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function removeTyping() {
    const t = document.getElementById("typingIndicator");
    if (t) t.remove();
}

async function sendChat() {
    const input   = document.getElementById("chatInput");
    const message = input.value.trim();
    if (!message) return;

    input.value = "";
    appendMessage("user", message);
    showTyping();
//send send to fask backend
    try {
        const res = await fetch("http://127.0.0.1:5000/chat", {
            method:  "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                message:      message,
                student_usn:  STUDENT_USN,
                student_name: STUDENT_NAME
            })
        });
        const data = await res.json();
        removeTyping();
        appendMessage("bot", data.reply || "⚠️ No response received.");

    } catch (err) {
        removeTyping();
        appendMessage("bot", "⚠️ Cannot connect to chatbot. Make sure chatbot.py is running on port 5000.");
    }
}

function sendQuick(text) {
    document.getElementById("chatInput").value = text;
    sendChat();
}

// ── Init ──────────────────────────────────────────────────────────────────────

loadStudentAnalytics();
loadRecommendations();
</script>
</body>
</html>
( ! ) Warning: Undefined variable $overdue in C:\wamp64\www\library\student\student_dashboard.php on line 100