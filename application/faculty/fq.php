<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student'])) {
    header("Location: ../../presentation/student_login.html");
    exit;
}

require_once(__DIR__ . "/../../data/db.php");

$s = $_SESSION['student'];
$student_id = (int)$s['id'];

$success = "";
$error = "";

// Find latest lecture attended by this student (or latest lecture overall)
$stmt = $conn->prepare("
    SELECT l.id, l.subject, l.faculty_id, l.lecture_date
    FROM lectures l
    ORDER BY l.id DESC
    LIMIT 1
");
$stmt->execute();
$res = $stmt->get_result();
$lecture = $res->fetch_assoc();
$stmt->close();

if (!$lecture) {
    die("No lecture found. Ask faculty to generate a lecture first.");
}

$lecture_id = (int)$lecture['id'];
$faculty_id = (int)$lecture['faculty_id'];
$subject = $lecture['subject'];

// FORM SUBMIT
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $teach    = (int)($_POST['teach'] ?? 0);
    $explain  = (int)($_POST['explain'] ?? 0);
    $interact = (int)($_POST['interact'] ?? 0);
    $query    = trim($_POST['query'] ?? '');

    if ($teach < 1 || $teach > 5 || $explain < 1 || $explain > 5 || $interact < 1 || $interact > 5) {
        $error = "Please select ratings between 1 and 5.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO feedback
            (student_id, lecture_id, faculty_id, teaching_rating, explanation_rating, interaction_rating, query, feedback_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "iiiiiss",
            $student_id,
            $lecture_id,
            $faculty_id,
            $teach,
            $explain,
            $interact,
            $query
        );

        if ($stmt->execute()) {
            $success = "Feedback submitted successfully ✅";
        } else {
            $error = "Insert failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Silent Feedback & Query</title>

<style>
body{
    background:#f4f6f9;
    font-family: Arial, sans-serif;
}
.container{
    width:80%;
    margin:40px auto;
}
.card{
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
h2{text-align:center;}
label{font-weight:bold;}
input,select,textarea{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
}
button{
    background:#0d6efd;
    color:white;
    padding:10px 25px;
    border:none;
    border-radius:6px;
}
.success{color:green;text-align:center;font-weight:bold;}
.error{color:red;text-align:center;font-weight:bold;}
</style>
</head>

<body>
<div class="container">
<div class="card">

<h2>Silent Feedback & Query</h2>
<p style="text-align:center;">
    Feedback for subject: <b><?php echo htmlspecialchars($subject); ?></b>
</p>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST">

<label>Teaching Quality (1-5)</label>
<select name="teach" required>
<option value="">Select</option>
<option>1</option><option>2</option><option>3</option>
<option>4</option><option>5</option>
</select>

<label>Explanation Clarity (1-5)</label>
<select name="explain" required>
<option value="">Select</option>
<option>1</option><option>2</option><option>3</option>
<option>4</option><option>5</option>
</select>

<label>Interaction (1-5)</label>
<select name="interact" required>
<option value="">Select</option>
<option>1</option><option>2</option><option>3</option>
<option>4</option><option>5</option>
</select>

<label>Query / Doubt (Optional)</label>
<textarea name="query" rows="4"></textarea>

<center><button type="submit">Submit</button></center>

</form>

</div>
</div>
</body>
</html>
