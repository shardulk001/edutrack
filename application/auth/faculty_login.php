<?php
session_start();
require_once(__DIR__ . "/../../data/db.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please fill all fields";
    } else {

        $stmt = $conn->prepare("
            SELECT id, password_hash
            FROM faculty
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (!empty($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['faculty_id'] = (int)$row['id'];
                $stmt->close();
                header("Location: ../faculty/faculty_dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Login</title>

<style>
*{
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}
body{
    margin: 0;
    height: 100vh;
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card{
    background: white;
    width: 350px;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.login-card h2{
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}
.login-card input{
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
.login-card input:focus{
    outline: none;
    border-color: #4e73df;
}
.login-card button{
    width: 100%;
    padding: 12px;
    background: #4e73df;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}
.login-card button:hover{
    background: #2e59d9;
}
.error{
    color: red;
    text-align: center;
    margin-bottom: 15px;
    font-size: 14px;
}
.footer-text{
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
    color: #666;
}
</style>
</head>

<body>

<div class="login-card">
    <h2>Faculty Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="email" placeholder="Faculty Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="footer-text">
        EduTrack © 2026
    </div>
</div>

</body>
</html>
