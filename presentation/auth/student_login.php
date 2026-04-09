<?php
session_start();
require_once(__DIR__ . "/../../data/db.php");

$email = trim($_POST['email'] ?? "");
$password = $_POST['password'] ?? "";

if ($email === "" || $password === "") {
    echo "Email and password are required";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if ($student && !empty($student['password_hash']) && password_verify($password, $student['password_hash'])) {
    $_SESSION['student'] = $student;
    $_SESSION['student_id'] = $student['id']; 
    header("Location: ../student/student_profile.php");
    exit;
} else {
    echo "Invalid email or password";
}
?>
