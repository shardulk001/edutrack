<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>admin Dashboard</title>

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
    width:380px;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    text-align:center;
}
.card h2{
    margin-bottom:10px;
    color:#333;
}
.card p{
    color:#666;
    margin-bottom:25px;
}
.card a{
    display:block;
    padding:12px;
    margin:10px 0;
    background:#4e73df;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:16px;
}
.card a:hover{
    background:#2e59d9;
}
.logout{
    background:#dc3545 !important;
}
.logout:hover{
    background:#b02a37 !important;
}
.footer{
    margin-top:15px;
    font-size:13px;
    color:#777;
}
</style>
</head>

<body>

<div class="card">
    <h2>Welcome, admin</h2>
    <p>Manage students from here</p>

    <a href="../admin/add_student.php">Add Student</a>
    <a href="../admin/add_faculty.php">Add Faculty</a>
    <a href="../admin/update_student.php">Update Student info and password</a>
    <a href="../admin/view_student.php">View / Delete Students</a>
   <!-- <a href="../admin/import_students.php">Bulk Import Students</a>-->
    
    <a href="../auth/admin_logout.php" class="logout">Logout</a>

    <div class="footer">
        EduTrack 2026
    </div>
</div>

</body>
</html>
