<?php
session_start();
// SHOW ALL ERRORS (DO NOT REMOVE)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student'])) {
    header("Location: login.html");
    exit;
}

$student_id = $_SESSION['student']['id'] ?? null;
if (!$student_id) {
    die("Student session is missing.");
}

include "db.php";

$success = "";
$error = "";

// FORM SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $class    = $_POST['class'];
    $dept     = $_POST['dept'];
    $subject  = $_POST['subject'];
    $teach    = $_POST['teach'];
    $explain  = $_POST['explain'];
    $interact = $_POST['interact'];
    $query    = $_POST['query'];
    $date     = date("Y-m-d");

    $stmt = $conn->prepare(
        "INSERT INTO feedback_query 
        (student_id, class, department, subject, teaching_rating, explanation_rating, interaction_rating, query, feedback_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if(!$stmt){
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssiiiss",
        $student_id,
        $class,
        $dept,
        $subject,
        $teach,
        $explain,
        $interact,
        $query,
        $date
    );

    if ($stmt->execute()) {
        $success = "Feedback and Query Submitted Successfully";
    } else {
        $error = "Insert failed: " . $stmt->error;
    }

    $stmt->close();
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
<p style="text-align:center;">Your response is completely anonymous</p>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST">

<label>Class</label>
<input type="text" name="class" required>

<label>Department</label>
<input type="text" name="dept" required>

<label>Subject</label>
<input type="text" name="subject" required>

<label>Teaching Quality (1-5)</label>
<select name="teach">
<option>1</option><option>2</option><option>3</option>
<option>4</option><option>5</option>
</select>

<label>Explanation Clarity (1-5)</label>
<select name="explain">
<option>1</option><option>2</option><option>3</option>
<option>4</option><option>5</option>
</select>

<label>Interaction (1-5)</label>
<select name="interact">
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
