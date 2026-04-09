<?php
session_start();
if (!isset($_SESSION['faculty_id'])) {
    header("Location: ../auth/faculty_login.php");
    exit;
}
$faculty_id = (int)$_SESSION['faculty_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="../../presentation/style.css">
</head>
<body>

<div class="navbar">
    <h2>EduTrack | Faculty Dashboard</h2>
</div>

<div class="container">
    <div class="card">

        <?php
        require_once(__DIR__ . "/../../data/db.php");
        $date = date('Y-m-d');

        mysqli_query($conn,"DELETE FROM lecture_code WHERE lecture_date='$date' AND faculty_id='$faculty_id'");

        for($i=1;$i<=4;$i++){
            $code = rand(1000,9999);
            mysqli_query($conn,
            "INSERT INTO lecture_code (faculty_id, lecture_no, lecture_date, code)
             VALUES ('$faculty_id','$i','$date','$code')");
            echo "Lecture $i Code: <b>$code</b><br>";
        }
        ?>

        <br>
        <a href="../student/student.php">
            <button class="btn">Go to Student Page</button>
        </a>

    </div>
</div>

</body>
</html>
