<?php
session_start();
require_once(__DIR__ . "/../../data/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, 'UTF-8');
}

function normalize_header($value) {
    $value = strtolower(trim($value));
    $value = str_replace([" ", "-", "."], "_", $value);
    return $value;
}

function find_index($header, $candidates) {
    foreach ($candidates as $c) {
        $idx = array_search($c, $header, true);
        if ($idx !== false) {
            return $idx;
        }
    }
    return null;
}

function generate_temp_password($length = 8) {
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    $max = strlen($chars) - 1;
    $password = "";
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    return $password;
}

$error = "";
$results = $_SESSION['import_results'] ?? null;
if ($results) {
    unset($_SESSION['import_results']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload a valid CSV file";
    } else {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            $error = "Failed to read the uploaded file";
        } else {
            $inserted = 0;
            $skipped = 0;
            $errors = 0;
            $generated = [];
            $error_rows = [];

            $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
            $insert_stmt = $conn->prepare(
                "INSERT INTO students (first_name, last_name, email, department, password_hash)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $header = null;
            $index_map = null;
            $line = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $line++;

                if (count($row) === 1 && trim($row[0]) === "") {
                    continue;
                }

                if ($line === 1) {
                    $normalized = array_map('normalize_header', $row);
                    if (in_array('email', $normalized, true)) {
                        $header = $normalized;
                        $index_map = [
                            'first_name' => find_index($header, ['first_name', 'firstname', 'fname']),
                            'last_name' => find_index($header, ['last_name', 'lastname', 'lname']),
                            'email' => find_index($header, ['email', 'email_address']),
                            'department' => find_index($header, ['department', 'dept']),
                            'password' => find_index($header, ['password', 'pass'])
                        ];
                        continue;
                    }
                }

                if ($index_map) {
                    $fname = trim($row[$index_map['first_name']] ?? "");
                    $lname = trim($row[$index_map['last_name']] ?? "");
                    $email = trim($row[$index_map['email']] ?? "");
                    $dept = trim($row[$index_map['department']] ?? "");
                    $password = trim($row[$index_map['password']] ?? "");
                } else {
                    $fname = trim($row[0] ?? "");
                    $lname = trim($row[1] ?? "");
                    $email = trim($row[2] ?? "");
                    $dept = trim($row[3] ?? "");
                    $password = trim($row[4] ?? "");
                }

                if ($fname === "" || $lname === "" || $email === "" || $dept === "") {
                    $errors++;
                    if (count($error_rows) < 20) {
                        $error_rows[] = "Line $line: missing required fields";
                    }
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors++;
                    if (count($error_rows) < 20) {
                        $error_rows[] = "Line $line: invalid email";
                    }
                    continue;
                }

                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $exists = $check_stmt->get_result();
                if ($exists && $exists->num_rows > 0) {
                    $skipped++;
                    continue;
                }

                $generated_password = false;
                if ($password === "") {
                    $password = generate_temp_password();
                    $generated_password = true;
                }

                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt->bind_param("sssss", $fname, $lname, $email, $dept, $password_hash);

                if ($insert_stmt->execute()) {
                    $inserted++;
                    if ($generated_password) {
                        $generated[] = [
                            'email' => $email,
                            'password' => $password
                        ];
                    }
                } else {
                    $errors++;
                    if (count($error_rows) < 20) {
                        $error_rows[] = "Line $line: database insert failed";
                    }
                }
            }

            fclose($handle);

            $_SESSION['import_results'] = [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'errors' => $errors,
                'generated' => $generated,
                'error_rows' => $error_rows
            ];

            header("Location: import_students.php?done=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bulk Import Students</title>
<style>
*{
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}
body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#4e73df,#1cc88a);
    padding:40px 0;
}
.card{
    background:white;
    width:90%;
    max-width:900px;
    margin:auto;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}
.card h2{
    text-align:center;
    margin-bottom:20px;
    color:#333;
}
.error{
    color:#dc3545;
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}
.success{
    background:#e9f7ef;
    border:1px solid #b7e4c7;
    color:#155724;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:14px;
}
.note{
    font-size:14px;
    color:#555;
    margin-bottom:15px;
}
form{
    margin-bottom:20px;
}
input[type="file"]{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    margin-bottom:12px;
}
button{
    padding:10px 16px;
    background:#4e73df;
    border:none;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
button:hover{
    background:#2e59d9;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td{
    padding:10px;
    border:1px solid #ccc;
    text-align:left;
    font-size:14px;
}
th{
    background:#4e73df;
    color:white;
}
.back{
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    color:#4e73df;
    font-weight:bold;
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
    <h2>Bulk Import Students</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($results): ?>
        <div class="success">
            Inserted: <b><?= h($results['inserted']) ?></b> | Skipped: <b><?= h($results['skipped']) ?></b> | Errors: <b><?= h($results['errors']) ?></b>
        </div>
    <?php endif; ?>

    <div class="note">
        CSV columns supported (header optional): <b>first_name, last_name, email, department, password</b>.<br>
        If password is empty, a temporary one will be generated.
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Import CSV</button>
    </form>

    <?php if ($results && !empty($results['generated'])): ?>
        <h3>Generated Passwords</h3>
        <table>
            <tr>
                <th>Email</th>
                <th>Temporary Password</th>
            </tr>
            <?php foreach ($results['generated'] as $row): ?>
                <tr>
                    <td><?= h($row['email']) ?></td>
                    <td><?= h($row['password']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($results && !empty($results['error_rows'])): ?>
        <h3>Import Errors (first 20)</h3>
        <table>
            <tr>
                <th>Issue</th>
            </tr>
            <?php foreach ($results['error_rows'] as $row): ?>
                <tr>
                    <td><?= h($row) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="back">Back to Dashboard</a>

    <div class="footer">
        EduTrack 2026
    </div>
</div>
</body>
</html>
