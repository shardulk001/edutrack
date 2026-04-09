<?php
session_start();
session_destroy();
header("Location: ../../presentation/student_login.html");
exit;
?>
