<?php
session_start();

if (!isset($_SESSION['student'])) {
    header("Location: ../../presentation/student_login.html");
    exit;
}

$s = $_SESSION['student'];
$student_id = $s['id'] ?? null;
$student_name = trim(($s['first_name'] ?? "") . " " . ($s['last_name'] ?? ""));
?>
<h2>Student Attendance Form</h2>

<form method="POST">
    Student:
    <input type="text" value="<?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?>" readonly><br><br>

    Student ID:
    <input type="text" value="<?php echo htmlspecialchars((string)$student_id, ENT_QUOTES, 'UTF-8'); ?>" readonly><br><br>

    Lecture 1 Code:
    <input type="text" name="c1" required><br><br>

    Lecture 2 Code:
    <input type="text" name="c2" required><br><br>

    Lecture 3 Code:
    <input type="text" name="c3" required><br><br>

    Lecture 4 Code:
    <input type="text" name="c4" required><br><br>

    <button name="submit">Submit Attendance</button>
</form>
<?php
require_once(__DIR__ . "/../../data/db.php");

if(isset($_POST['submit'])){
    if (!$student_id) {
        echo "<b>Student session is missing.</b>";
        exit;
    }

    $date  = date('Y-m-d');

    $codes = [
        1 => trim($_POST['c1'] ?? ""),
        2 => trim($_POST['c2'] ?? ""),
        3 => trim($_POST['c3'] ?? ""),
        4 => trim($_POST['c4'] ?? "")
    ];

    $check_stmt = $conn->prepare(
        "SELECT a.id
         FROM attendance a
         JOIN lecture_code l ON a.lecture_id = l.id
         WHERE a.student_id = ? AND l.lecture_date = ?
         LIMIT 1"
    );
    $check_stmt->bind_param("is", $student_id, $date);
    $check_stmt->execute();
    $already = $check_stmt->get_result();

    if ($already && $already->num_rows > 0) {
        echo "Attendance already submitted";
        exit;
    }

    $lecture_stmt = $conn->prepare(
        "SELECT id, code FROM lecture_code WHERE lecture_no = ? AND lecture_date = ? LIMIT 1"
    );
    $insert_stmt = $conn->prepare(
        "INSERT INTO attendance (student_id, lecture_id, status) VALUES (?, ?, ?)"
    );

    $missing = [];
    foreach ($codes as $lec => $code) {
        $lecture_stmt->bind_param("is", $lec, $date);
        $lecture_stmt->execute();
        $lecture = $lecture_stmt->get_result()->fetch_assoc();

        if (!$lecture) {
            $missing[] = $lec;
            continue;
        }

        $status = ((string)$lecture['code'] === (string)$code) ? "Present" : "Absent";
        $lecture_id = (int)$lecture['id'];

        $insert_stmt->bind_param("iis", $student_id, $lecture_id, $status);
        $insert_stmt->execute();
    }

    echo "<b>Attendance Submitted Successfully</b>";
    if ($missing) {
        echo "<br><small>No lecture found for: " . htmlspecialchars(implode(", ", $missing), ENT_QUOTES, 'UTF-8') . "</small>";
    }
}

?>
