<?php
require_once(__DIR__ . "/../../data/db.php");

function get_columns(mysqli $conn, string $table): array {
    $cols = [];
    $result = $conn->query("SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cols[] = $row['Field'];
        }
    }
    return $cols;
}

if(isset($_POST["btn"])){

    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['pos'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fname !== '' && $lname !== '' && $email !== '' && $course !== '' && $password !== '') {

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "INSERT INTO faculty(firstname, lastname, email, pos)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $fname, $lname, $email, $course);
            $stmt->execute();
            $faculty_id = (int)$stmt->insert_id;
            $stmt->close();

            $userCols = get_columns($conn, 'faculty_users');
            if (!empty($userCols)) {
                $insertCols = [];
                $values = [];
                $types = '';

                if (in_array('faculty_id', $userCols, true)) {
                    $insertCols[] = 'faculty_id';
                    $values[] = $faculty_id;
                    $types .= 'i';
                }
                if (in_array('password_hash', $userCols, true)) {
                    $insertCols[] = 'password_hash';
                    $values[] = password_hash($password, PASSWORD_DEFAULT);
                    $types .= 's';
                } elseif (in_array('password', $userCols, true)) {
                    $insertCols[] = 'password';
                    $values[] = $password;
                    $types .= 's';
                }
                if (in_array('firstname', $userCols, true)) {
                    $insertCols[] = 'firstname';
                    $values[] = $fname;
                    $types .= 's';
                }
                if (in_array('lastname', $userCols, true)) {
                    $insertCols[] = 'lastname';
                    $values[] = $lname;
                    $types .= 's';
                }
                if (in_array('email', $userCols, true)) {
                    $insertCols[] = 'email';
                    $values[] = $email;
                    $types .= 's';
                }
                if (in_array('pos', $userCols, true)) {
                    $insertCols[] = 'pos';
                    $values[] = $course;
                    $types .= 's';
                }

                if (!empty($insertCols)) {
                    $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
                    $sql = "INSERT INTO faculty_users (" . implode(',', $insertCols) . ") VALUES ({$placeholders})";
                    $stmt2 = $conn->prepare($sql);
                    $stmt2->bind_param($types, ...$values);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }

            $conn->commit();
            header('location:fdisplay.php');
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Faculty</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .form-card{
            background: #fff;
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .form-card h2{
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-control{
            padding: 12px;
        }

        .btn-primary{
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
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

<div class="form-card">
    <h2>Add Faculty</h2>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="fname" class="form-control"
                   placeholder="Enter Faculty First Name" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="lname" class="form-control"
                   placeholder="Enter Faculty Last Name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="Enter Faculty Email" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" name="pos" class="form-control"
                   placeholder="Enter Faculty Department" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Set Faculty Password" required>
        </div>

        <button type="submit" name="btn" class="btn btn-primary">
            Submit
        </button>
    </form>

    <div class="footer-text">
        EduTrack (c) 2026
    </div>
</div>

</body>
</html>
