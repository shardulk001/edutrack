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
    $pos   = trim($_POST['pos'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($fname === "" || $lname === "" || $email === "" || $pos === "" || $password === "") {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if email already exists in faculty
        $check = $conn->prepare("SELECT id FROM faculty WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result();

        if ($exists && $exists->num_rows > 0) {
            $error = "Email already exists";
        } else {

            // ✅ Hash password BEFORE insert
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Insert directly into faculty (including password_hash)
            $stmt = $conn->prepare(
                "INSERT INTO faculty (firstname, lastname, email, pos, password_hash)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $fname, $lname, $email, $pos, $password_hash);

            if ($stmt->execute()) {

                // ❌ REMOVED faculty_users table logic completely

                header("Location: view_faculty.php");
                exit;
            } else {
                $error = "Failed to add faculty";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Faculty</title>

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
    <h2>Add Faculty</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="pos" placeholder="Position (e.g. Assistant Professor)" required>
        <input type="password" name="password" placeholder="Temporary Password" minlength="6" required>

        <input type="submit" name="add" value="Add Faculty" id="button">
    </form>

    <style>
.card input[type="submit"]{
    width:100%;
    padding:12px;
    margin:10px 0;
    background:#4e73df;
    color:white;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
}
.card input[type="submit"]:hover{
    background:#2e59d9;
}
</style>

    <div class="footer">
        EduTrack (c) 2026
    </div>
</div>

</body>
</html>