<?php
session_start();
require_once(__DIR__ . "/../../data/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_student.php");
    exit;
}

$id = (int)$_GET['id'];
$return = $_GET['return'] ?? "view_student.php";
$q = trim($_GET['q'] ?? "");

$allowed_returns = ["view_student.php", "update_student.php"];
if (!in_array($return, $allowed_returns, true)) {
    $return = "view_student.php";
}

$stmt = $conn->prepare("SELECT email FROM students WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    header("Location: " . $return);
    exit;
}

$email = $row['email'];

function generate_temp_password($length = 8) {
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    $max = strlen($chars) - 1;
    $password = "";
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    return $password;
}

$new_password = generate_temp_password();
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$upd = $conn->prepare("UPDATE students SET password_hash = ? WHERE id = ?");
$upd->bind_param("si", $password_hash, $id);
$upd->execute();

$_SESSION['reset_notice'] = [
    'email' => $email,
    'password' => $new_password
];

$redirect = $return;
if ($q !== "") {
    $redirect .= "?q=" . urlencode($q);
}

header("Location: " . $redirect);
exit;
?>
