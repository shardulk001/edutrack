<?php
session_start();

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SIMPLE STATIC ADMIN CREDENTIALS
    if($username === "admin" && $password === "admin123"){
        $_SESSION['admin'] = true;
        header("Location: ../admin/admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>

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
    <h2>Admin Login</h2>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Admin Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <div class="footer-text">
        EduTrack (c) 2026
    </div>
</div>

</body>
</html>
