<?php

session_start();

require_once("../config/db.php");

/* CHECK LOGIN */
if (!isset($_SESSION['student_usn'])) {
    header("Location: login.php");
    exit();
}

$student_usn = $_SESSION['student_usn'];

/* FETCH STUDENT DETAILS */
$stmt = $conn->prepare("SELECT * FROM students WHERE student_usn = ?");
$stmt->bind_param("s", $student_usn);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* UPDATE PROFILE */
if (isset($_POST['update_profile'])) {

    $student_name = trim($_POST['student_name']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);

    // Basic validation
    if (empty($student_name) || empty($email) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $conn->prepare("UPDATE students SET student_name=?, email=?, phone=? WHERE student_usn=?");
        $stmt->bind_param("ssss", $student_name, $email, $phone, $student_usn);
        $stmt->execute();
        $stmt->close();

        /* UPDATE SESSION */
        $_SESSION['student_name']  = $student_name;
        $_SESSION['student_email'] = $email;
        $_SESSION['student_phone'] = $phone;

        header("Location: profile.php?success=1");
        exit();
    }
}

$success = isset($_GET['success']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student.css">
    <style>

        .profile-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
            padding-bottom: 10px;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .profile-table th {
            width: 35%;
        }

        .edit-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            font-size: 15px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .edit-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }

        .hidden {
            display: none;
        }

        /* Alert messages */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.12), rgba(46, 204, 113, 0.08));
            border: 1px solid rgba(39, 174, 96, 0.3);
            color: #27ae60;
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.12), rgba(192, 57, 43, 0.08));
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

    </style>
</head>

<body>

<div class="dashboard">

    <?php require_once("student_sidebar.php"); ?>

    <div class="main">

        <!-- HEADER -->
        <div class="header">
            <div>
                <h2>🎓 My Profile</h2>
            </div>

            <!-- USER MENU -->
            <div class="user-menu">
                <button class="user-btn" onclick="toggleDropdown(event)">
                    👤 <?= htmlspecialchars(strtoupper($student['student_name'])); ?>
                </button>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if ($success): ?>
            <div class="alert alert-success">✅ Profile updated successfully.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- PROFILE TABLE -->
        <div class="table-container">

            <div class="profile-table-header">
                <h3>Student Details</h3>
                <div style="display:flex; align-items:center; gap:15px;">
                    <button type="button" class="edit-btn" onclick="enableEdit()">
                        ✏️ Edit Profile
                    </button>
                    <a href="student_dashboard.php">
                        <button type="button" class="return-btn">
                            ← Back to Dashboard
                        </button>
                    </a>
                </div>
            </div>

            <form method="POST">

                <table class="profile-table">
                    <tr>
                        <th>Field Name</th>
                        <th>Details</th>
                    </tr>
                    <tr>
                        <td>Student Name</td>
                        <td>
                            <span class="view-mode"><?= htmlspecialchars($student['student_name']); ?></span>
                            <input type="text" name="student_name"
                                   value="<?= htmlspecialchars($student['student_name']); ?>"
                                   class="edit-input hidden" required>
                        </td>
                    </tr>
                    <tr>
                        <td>USN</td>
                        <td><?= htmlspecialchars($student['student_usn']); ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>
                            <span class="view-mode"><?= htmlspecialchars($student['email']); ?></span>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($student['email']); ?>"
                                   class="edit-input hidden" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Phone Number</td>
                        <td>
                            <span class="view-mode"><?= htmlspecialchars($student['phone']); ?></span>
                            <input type="text" name="phone"
                                   value="<?= htmlspecialchars($student['phone']); ?>"
                                   class="edit-input hidden" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Account Status</td>
                        <td><span class="status available">Active</span></td>
                    </tr>
                </table>

                <!-- ACTION BUTTONS -->
                <div class="profile-actions hidden" id="profileActions">
                    <button type="submit" name="update_profile" class="edit-btn">
                        Save Changes
                    </button>
                    <button type="button" class="delete-btn" onclick="cancelEdit()">
                        Cancel
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<script>

    /* DROPDOWN */
    function toggleDropdown(event) {
        event.stopPropagation();
        document.getElementById("dropdownMenu").classList.toggle("active");
    }

    window.onclick = function () {
        document.getElementById("dropdownMenu").classList.remove("active");
    };

    /* EDIT PROFILE */
    function enableEdit() {
        document.querySelectorAll(".edit-input").forEach(input => {
            input.classList.remove("hidden");
        });
        document.querySelectorAll(".view-mode").forEach(text => {
            text.style.display = "none";
        });
        document.getElementById("profileActions").classList.remove("hidden");
    }

    /* CANCEL EDIT */
    function cancelEdit() {
        location.reload();
    }

</script>

</body>
</html>