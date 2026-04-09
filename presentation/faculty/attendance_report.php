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

// escape helper
function h($v) {
    return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8');
}

$facultyId = (int)$_SESSION['faculty_id'];

// If a lecture is selected, we will show students for that lecture
$selectedLectureId = isset($_GET['lecture_id']) ? (int)$_GET['lecture_id'] : 0;

/* ===============================
   FETCH LECTURES FOR THIS FACULTY
================================ */
$stmt = $conn->prepare("
    SELECT l.id, l.subject, l.lecture_no, l.lecture_date,
           COUNT(a.id) AS present_count
    FROM lectures l
    LEFT JOIN attendance a ON a.lecture_id = l.id
    WHERE l.faculty_id = ?
    GROUP BY l.id, l.subject, l.lecture_no, l.lecture_date
    ORDER BY l.lecture_date DESC, l.id DESC
");
$stmt->bind_param("i", $facultyId);
$stmt->execute();
$lecturesResult = $stmt->get_result();
$stmt->close();

/* ===============================
   FETCH STUDENTS FOR SELECTED LECTURE
================================ */
$studentsResult = null;
$lectureInfo = null;

if ($selectedLectureId > 0) {

    // Get lecture info (make sure it belongs to this faculty)
    $stmt = $conn->prepare("
        SELECT id, subject, lecture_no, lecture_date
        FROM lectures
        WHERE id = ? AND faculty_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $selectedLectureId, $facultyId);
    $stmt->execute();
    $res = $stmt->get_result();
    $lectureInfo = $res->fetch_assoc();
    $stmt->close();

    if ($lectureInfo) {
        // Get students who attended this lecture
        $stmt = $conn->prepare("
            SELECT s.id, s.first_name, s.last_name, s.email, a.status
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            WHERE a.lecture_id = ?
            ORDER BY s.first_name, s.last_name
        ");
        $stmt->bind_param("i", $selectedLectureId);
        $stmt->execute();
        $studentsResult = $stmt->get_result();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Attendance Report</title>
<link rel="stylesheet" href="../../presentation/style.css">

<style>
table{width:100%;border-collapse:collapse;margin-bottom:20px;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#0d6efd;color:#fff;}
tr:nth-child(even){background:#f9f9f9;}
.container{padding:20px;}
.card{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px;}
.btn{
    padding:6px 12px;
    background:#198754;
    color:white;
    text-decoration:none;
    border-radius:4px;
}
.btn-secondary{
    background:#0d6efd;
}
</style>
</head>

<body>

<div class="navbar">
    <h2>EduTrack | Attendance Report</h2>
    <a href="faculty_dashboard.php">Back to Dashboard</a>
</div>

<div class="container">

<!-- LECTURE LIST -->
<div class="card">
    <h3>Lectures (Your Classes)</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Subject</th>
            <th>Lecture No</th>
            <th>Date</th>
            <th>Present Count</th>
            <th>View</th>
        </tr>

        <?php if ($lecturesResult && $lecturesResult->num_rows > 0): ?>
            <?php while ($l = $lecturesResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int)$l['id']; ?></td>
                    <td><?php echo h($l['subject']); ?></td>
                    <td><?php echo (int)$l['lecture_no']; ?></td>
                    <td><?php echo h($l['lecture_date']); ?></td>
                    <td><?php echo (int)$l['present_count']; ?></td>
                    <td>
                        <a class="btn btn-secondary" href="attendance_report.php?lecture_id=<?php echo (int)$l['id']; ?>">
                            View Students
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No lectures found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<!-- STUDENT LIST FOR SELECTED LECTURE -->
<?php if ($lectureInfo): ?>
<div class="card">
    <h3>
        Students for:
        <?php echo h($lectureInfo['subject']); ?>
        (Lecture <?php echo (int)$lectureInfo['lecture_no']; ?>,
        <?php echo h($lectureInfo['lecture_date']); ?>)
    </h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
        </tr>

        <?php if ($studentsResult && $studentsResult->num_rows > 0): ?>
            <?php while ($s = $studentsResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td><?php echo h($s['first_name'] . " " . $s['last_name']); ?></td>
                    <td><?php echo h($s['email']); ?></td>
                    <td><?php echo h($s['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No attendance records for this lecture.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
<?php endif; ?>

</div>

</body>
</html>
