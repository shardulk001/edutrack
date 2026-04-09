<?php
// SAFE session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// protect page
if (!isset($_SESSION['faculty_id'])) {
    header("Location: ../auth/faculty_login.php");
    exit;
}

require_once(__DIR__ . "/../../data/db.php");

// safe escape function
if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8');
    }
}

/* ===============================
   DELETE FEEDBACK (SAFE)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $delete_id = (int)$_POST['delete_id'];

    if ($delete_id > 0) {
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Delete failed: " . $conn->error);
        }
    }

    header("Location: faculty_dashboard.php");
    exit;
}

/* ===============================
   LECTURE + ATTENDANCE CODE SYSTEM
================================ */

$generatedMessage = '';
$latestCode = '';
$latestSubject = '';
$latestExpires = '';

$facultyId = (int)$_SESSION['faculty_id'];

// Generate code
if (isset($_POST['generate_code'])) {

    $code = (string)rand(100000, 999999);
    $subject = trim($_POST['subject'] ?? '');

    if ($subject === '') {
        $generatedMessage = "Please select a subject.";
    } else {

        // Find next lecture number for this faculty + subject
        $stmt = $conn->prepare("
            SELECT COALESCE(MAX(lecture_no), 0) + 1 AS next_no
            FROM lectures
            WHERE faculty_id = ? AND subject = ?
        ");
        $stmt->bind_param("is", $facultyId, $subject);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $nextLectureNo = (int)$row['next_no'];
        $stmt->close();

        // Insert new lecture
        $stmt = $conn->prepare("
            INSERT INTO lectures (faculty_id, subject, lecture_no, lecture_date, code, expires_at)
            VALUES (?, ?, ?, CURDATE(), ?, DATE_ADD(NOW(), INTERVAL 2 MINUTE))
        ");

        if (!$stmt) {
            $generatedMessage = "DB error: " . $conn->error;
        } else {
            $stmt->bind_param("isis", $facultyId, $subject, $nextLectureNo, $code);

            if ($stmt->execute()) {
                $generatedMessage = "Code generated for $subject (Lecture $nextLectureNo)";
            } else {
                $generatedMessage = "Error generating code.";
            }
            $stmt->close();
        }
    }
}

// Fetch latest active code for this faculty
$stmt = $conn->prepare("
    SELECT code, subject, expires_at
    FROM lectures
    WHERE faculty_id = ?
      AND expires_at > NOW()
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $facultyId);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    $latestCode = $row['code'];
    $latestSubject = $row['subject'];
    $latestExpires = $row['expires_at'];
}
$stmt->close();

/* ===============================
   FETCH FEEDBACK (NEW SYSTEM)
================================ */

$stmt = $conn->prepare("
    SELECT f.id, f.teaching_rating, f.explanation_rating, f.interaction_rating,
           f.query, f.feedback_date,
           s.first_name, s.last_name,
           l.subject, l.lecture_date
    FROM feedback f
    JOIN students s ON s.id = f.student_id
    JOIN lectures l ON l.id = f.lecture_id
    WHERE f.faculty_id = ?
    ORDER BY f.id DESC
");
$stmt->bind_param("i", $facultyId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Faculty Dashboard</title>
<link rel="stylesheet" href="../../presentation/style.css">

<style>
.notice{
    background:#e7f1ff;
    border:1px solid #b6d4fe;
    color:#084298;
    padding:10px;
    border-radius:6px;
    margin-bottom:10px;
}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#0d6efd;color:#fff;}
tr:nth-child(even){background:#f9f9f9;}
.query{text-align:left;}
.btn-sm{
    padding:1px 6px;
    background:#198754;
    color:white;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="navbar">
    <h2>EduTrack | Faculty Dashboard</h2>
    <a href="attendance_report.php" style="margin-right:15px;color:white;">Attendance Report</a>
    <a href="../auth/faculty_logout.php">Logout</a>
</div>



<div class="container">

<!-- ATTENDANCE CODE -->
<div class="card">
<h2 style="text-align:center;">Attendance Code</h2>

<?php if ($generatedMessage) { ?>
<div class="notice"><?php echo h($generatedMessage); ?></div>
<?php } ?>

<?php if ($latestCode) { ?>
<p><b>Subject:</b> <?php echo h($latestSubject); ?></p>
<p><b>Code:</b>
<span style="font-size:22px;color:#0d6efd;">
<?php echo h($latestCode); ?>
</span>
</p>
<p>Valid until: <?php echo h($latestExpires); ?></p>
<?php } else { ?>
<p>No active code.</p>
<?php } ?>

<form method="post">
    <select name="subject" required>
        <option value="">Select Subject</option>
        <option>Operating Systems</option>
        <option>Java Programming</option>
        <option>Database Management</option>
        <option>Computer Networks</option>
    </select>
    <button class="btn-sm" name="generate_code">Generate Code</button>
</form>

</div>

<!-- FEEDBACK SECTION -->
<div class="card">
<h2 style="text-align:center;">All Student Feedback & Queries</h2>

<table>
<tr>
<th>ID</th>
<th>Student</th>
<th>Subject</th>
<th>Lecture Date</th>
<th>Teaching</th>
<th>Explanation</th>
<th>Interaction</th>
<th>Query</th>
<th>Feedback Date</th>
<th>Solved</th>
</tr>



<?php
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".h($row['first_name']." ".$row['last_name'])."</td>";
        echo "<td>".h($row['subject'])."</td>";
        echo "<td>".h($row['lecture_date'])."</td>";
        echo "<td>".$row['teaching_rating']."</td>";
        echo "<td>".$row['explanation_rating']."</td>";
        echo "<td>".$row['interaction_rating']."</td>";
        echo "<td class='query'>".h($row['query'] ?: "-")."</td>";
        echo "<td>".$row['feedback_date']."</td>";
        echo "<td>
                <form method='POST'>
                <input type='hidden' name='delete_id' value='".$row['id']."'>
                <input type='checkbox'
                onchange='if(confirm(\"Delete this feedback?\") ) this.form.submit();'>
                </form>
              </td>";
        echo "</tr>";
    }
}else{
    echo "<tr><td colspan='10'>No records</td></tr>";
}
?>

</table>
</div>
<div class="card" style="text-align:center;">
    <h4>Reports</h4>
    <a href="attendance_report.php" class="btn-sm"> Attendance Report</a>
</div>


</div>

</body>
</html>
