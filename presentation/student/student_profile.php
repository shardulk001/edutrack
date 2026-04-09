<?php
// safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student'])) {
    header("Location: ../../presentation/student_login.html");
    exit;
}

require_once(__DIR__ . "/../../data/db.php");

$s = $_SESSION['student'];
$studentEmail = $s['email'];
$attendanceMessage = '';

/* ===============================
   ATTENDANCE VALIDATION
================================ */
/* ===============================
   ATTENDANCE VALIDATION (NEW SYSTEM)
================================ */

$studentId = (int)$s['id'];

if (isset($_POST['validate_code'])) {

    $inputCode = trim($_POST['attendance_code'] ?? '');

    if ($inputCode === '') {
        $attendanceMessage = "Enter attendance code.";
    } else {

        // Find valid lecture by code
        $stmt = $conn->prepare("
            SELECT id
            FROM lectures
            WHERE code = ?
              AND expires_at > NOW()
            LIMIT 1
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $inputCode);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $row = $res->fetch_assoc()) {
            $lectureId = (int)$row['id'];

            // Check if already marked
            $check = $conn->prepare("
                SELECT id FROM attendance
                WHERE student_id = ? AND lecture_id = ?
            ");
            $check->bind_param("ii", $studentId, $lectureId);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $attendanceMessage = "Attendance already marked for this lecture.";
            } else {

                // Insert attendance
                $insert = $conn->prepare("
                    INSERT INTO attendance (student_id, lecture_id, status)
                    VALUES (?, ?, 'Present')
                ");
                $insert->bind_param("ii", $studentId, $lectureId);

                if ($insert->execute()) {
                    $attendanceMessage = "Attendance marked PRESENT ✅";
                } else {
                    $attendanceMessage = "Error marking attendance.";
                }
                $insert->close();
            }
            $check->close();

        } else {
            $attendanceMessage = "Invalid or expired code ❌";
        }

        $stmt->close();
    }
}


/* ===============================
   STUDENT DETAILS
================================ */
$full_name = trim(($s['first_name'] ?? "") . " " . ($s['last_name'] ?? ""));
if ($full_name === "") $full_name = "Not Provided";

$email = $s['email'] ?? "Not Provided";
$department = $s['department'] ?? "Not Provided";
$semester = $s['semester'] ?? "Not Set";

/* ===============================
   TODAY ATTENDANCE STATUS
================================ */
$todayStatus = "";
$studentId = (int)$s['id'];

$statusQuery = $conn->query("
    SELECT a.status
    FROM attendance a
    INNER JOIN lectures l ON l.id = a.lecture_id
    WHERE a.student_id = $studentId
      AND l.lecture_date = CURDATE()
    LIMIT 1
");


if ($statusQuery && $row = $statusQuery->fetch_assoc()) {
    $todayStatus = $row['status'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../../presentation/style.css">
</head>
<body>

<div class="navbar">
    <h2>EduTrack | Student Dashboard</h2>
    <a href="../auth/student_logout.php">Logout</a>
</div>

<div class="container">

    <!-- PROFILE -->
    <div class="card">
        <h3>Student Profile</h3>
        <p><b>Name:</b> <?php echo htmlspecialchars($full_name); ?></p>
        <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
        <p><b>Department:</b> <?php echo htmlspecialchars($department); ?></p>
        <p><b>Semester:</b> <?php echo htmlspecialchars($semester); ?></p>

        <?php if ($todayStatus) { ?>
            <p style="color:green;">
                <b>Today's Attendance: <?php echo $todayStatus; ?></b>
            </p>
        <?php } ?>
    </div>

    <!-- ACTIONS -->
    <div class="card">
        <h3>Actions</h3>
        <a class="btn" href="../faculty/fq.php">Give Feedback</a>
    </div>

    <!-- ATTENDANCE -->
    <div class="card">
        <h3>Attendance Code</h3>

        <?php if ($attendanceMessage) { ?>
            <p><b><?php echo htmlspecialchars($attendanceMessage); ?></b></p>
        <?php } ?>

        <form method="POST">
            <input type="text" name="attendance_code" placeholder="Enter 6-digit code" required>
            <button class="btn" name="validate_code">Submit</button>
        </form>
    </div>

</div>

</body>
</html>
