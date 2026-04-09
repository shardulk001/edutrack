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

$error = "";
$reset_notice = $_SESSION['reset_notice'] ?? null;
if ($reset_notice) {
    unset($_SESSION['reset_notice']);
}

if (!isset($_GET['id'])) {
    $search = trim($_GET['q'] ?? "");

    if ($search !== "") {
        $like = "%" . $search . "%";
        $stmt = $conn->prepare(
            "SELECT id, first_name, last_name, email, department
             FROM students
             WHERE first_name LIKE ?
                OR last_name LIKE ?
                OR email LIKE ?
                OR department LIKE ?
                OR CAST(id AS CHAR) LIKE ?
             ORDER BY id DESC"
        );
        $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
        $stmt->execute();
        $list = $stmt->get_result();
    } else {
        $list = $conn->query("SELECT id, first_name, last_name, email, department FROM students ORDER BY id DESC");
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Update Student</title>
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
        max-width:1000px;
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
    .notice{
        background:#fff3cd;
        border:1px solid #ffeeba;
        color:#856404;
        padding:10px 12px;
        border-radius:6px;
        margin-bottom:15px;
        font-size:14px;
    }
    .search{
        display:flex;
        gap:10px;
        margin-bottom:20px;
    }
    .search input{
        flex:1;
        padding:10px;
        border:1px solid #ccc;
        border-radius:6px;
    }
    .search button{
        padding:10px 16px;
        background:#4e73df;
        border:none;
        color:white;
        border-radius:6px;
        cursor:pointer;
    }
    .search button:hover{
        background:#2e59d9;
    }
    .search a.clear{
        display:inline-block;
        padding:10px 12px;
        background:#6c757d;
        color:white;
        text-decoration:none;
        border-radius:6px;
    }
    .search a.clear:hover{
        background:#5a6268;
    }
    table{
        width:100%;
        border-collapse:collapse;
    }
    th, td{
        padding:12px;
        border:1px solid #ccc;
        text-align:center;
    }
    th{
        background:#4e73df;
        color:white;
    }
    a.edit{
        color:#0d6efd;
        text-decoration:none;
        font-weight:bold;
        margin-right:10px;
    }
    a.reset{
        color:#fd7e14;
        text-decoration:none;
        font-weight:bold;
    }
    a.edit:hover,
    a.reset:hover{
        text-decoration:underline;
    }
    .back{
        display:inline-block;
        margin-top:20px;
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
        <h2>Select Student to Update</h2>

        <?php if ($reset_notice): ?>
            <div class="notice">
                Password reset for <b><?= h($reset_notice['email']) ?></b>. Temporary password: <b><?= h($reset_notice['password']) ?></b>
            </div>
        <?php endif; ?>

        <form class="search" method="get" action="update_student.php">
            <input type="text" name="q" placeholder="Search by name, email, dept, or ID" value="<?= h($search) ?>">
            <button type="submit">Search</button>
            <?php if ($search !== ""): ?>
                <a class="clear" href="update_student.php">Clear</a>
            <?php endif; ?>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Dept</th>
                <th>Actions</th>
            </tr>
            <?php if ($list && $list->num_rows > 0) { ?>
                <?php while ($row = $list->fetch_assoc()) { ?>
                <?php
                    $full_name = trim(($row['first_name'] ?? "") . " " . ($row['last_name'] ?? ""));
                    $name_html = $full_name !== "" ? h($full_name) : "<i>Not Provided</i>";
                    $return_page = "update_student.php";
                    $return_q = $search !== "" ? "&q=" . urlencode($search) : "";
                ?>
                <tr>
                    <td><?= h($row['id']) ?></td>
                    <td><?= $name_html ?></td>
                    <td><?= h($row['email']) ?></td>
                    <td><?= h($row['department']) ?></td>
                    <td>
                        <a class="edit" href="update_student.php?id=<?= h($row['id']) ?>">Update</a>
                        <a class="reset" href="reset_student_password.php?id=<?= h($row['id']) ?>&return=<?= $return_page ?><?= $return_q ?>"
                           onclick="return confirm('Reset password for this student?')">
                           Reset Password
                        </a>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5">No records found</td>
                </tr>
            <?php } ?>
        </table>

        <a href="admin_dashboard.php" class="back">Back to Dashboard</a>

        <div class="footer">
            EduTrack 2026
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header("Location: update_student.php");
    exit;
}

if (isset($_POST['update'])) {
    $fname = trim($_POST['fname'] ?? "");
    $lname = trim($_POST['lname'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $dept  = trim($_POST['department'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($fname === "" || $lname === "" || $email === "" || $dept === "") {
        $error = "All fields except password are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE email = ? AND id <> ? LIMIT 1");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $exists = $check->get_result();

        if ($exists && $exists->num_rows > 0) {
            $error = "Email already exists";
        } else {
            if ($password !== "") {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare(
                    "UPDATE students SET first_name=?, last_name=?, email=?, department=?, password_hash=? WHERE id=?"
                );
                $upd->bind_param("sssssi", $fname, $lname, $email, $dept, $password_hash, $id);
            } else {
                $upd = $conn->prepare(
                    "UPDATE students SET first_name=?, last_name=?, email=?, department=? WHERE id=?"
                );
                $upd->bind_param("ssssi", $fname, $lname, $email, $dept, $id);
            }

            if ($upd->execute()) {
                header("Location: view_student.php");
                exit;
            }

            $error = "Failed to update student";
        }
    }

    $student['first_name'] = $fname;
    $student['last_name'] = $lname;
    $student['email'] = $email;
    $student['department'] = $dept;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Student</title>
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
.notice{
    background:#fff3cd;
    border:1px solid #ffeeba;
    color:#856404;
    padding:10px 12px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:14px;
    text-align:center;
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
    <h2>Update Student</h2>

    <?php if ($reset_notice): ?>
        <div class="notice">
            Password reset for <b><?= h($reset_notice['email']) ?></b>. Temporary password: <b><?= h($reset_notice['password']) ?></b>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="fname" placeholder="First Name" value="<?= h($student['first_name']) ?>" required>
        <input type="text" name="lname" placeholder="Last Name" value="<?= h($student['last_name']) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= h($student['email']) ?>" required>
        <input type="text" name="department" placeholder="Department" value="<?= h($student['department']) ?>" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep)">

        <button type="submit" name="update">Update Student</button>
    </form>

    <a href="update_student.php" class="back">Back to List</a>
    <a href="admin_dashboard.php" class="back">Back to Dashboard</a>

    <div class="footer">
        EduTrack 2026
    </div>
</div>

</body>
</html>
