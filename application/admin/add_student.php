<?php
session_start();
require_once(__DIR__ . "/../../data/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$error = "";

if (isset($_POST['add'])) {
    $fname = trim($_POST['fname'] ?? "");
    $lname = trim($_POST['lname'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $dept  = trim($_POST['department'] ?? "");
    $semester = trim($_POST['semester'] ?? "");

    $password = $_POST['password'] ?? "";

    if ($fname === "" || $lname === "" || $email === "" || $dept === "" || $password === "" || $semester === "") {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result();

        if ($exists && $exists->num_rows > 0) {
            $error = "Email already exists";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO students (first_name, last_name, email, department, semester, password_hash)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssss", $fname, $lname, $email, $dept, $semester, $password_hash);

            if ($stmt->execute()) {
                header("Location: view_student.php");
                exit;
            }

            $error = "Failed to add student";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student</title>

<style>
*{
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}
body{
    margin:0;
    height:100vh;
    background:linear-gradient(135deg,#4e73df,#1cc88a);
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    background:white;
    width:420px;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}
.card h2{
    text-align:center;
    margin-bottom:25px;
    color:#333;
}
.error{
    color:red;
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}
input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
}
input:focus{
    outline:none;
    border-color:#4e73df;
}
button{
    width:100%;
    padding:12px;
    background:#4e73df;
    border:none;
    color:white;
    font-size:16px;
    border-radius:6px;
    cursor:pointer;
}
button:hover{
    background:#2e59d9;
}
.back{
    display:block;
    margin-top:15px;
    text-align:center;
    text-decoration:none;
    color:#4e73df;
    font-size:14px;
}
.footer{
    text-align:center;
    margin-top:15px;
    font-size:13px;
    color:#777;
}
</style>
</head>

<body>

<div class="card">
    <h2>Add Student</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="department" placeholder="Department" required>
        <input type="text" name="semester" placeholder="Semester" required>
        <input type="password" name="password" placeholder="Temporary Password" minlength="6" required>

        <button type="submit" name="add">Add Student</button>
    </form>

    <a href="admin_dashboard.php" class="back">Back to Dashboard</a>

    <div class="footer">
        EduTrack (c) 2026
    </div>
</div>

</body>
</html>
