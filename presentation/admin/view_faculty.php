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

$search = trim($_GET['q'] ?? "");

// Search logic
if ($search !== "") {
    $like = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM faculty
         WHERE firstname LIKE ?
            OR lastname LIKE ?
            OR email LIKE ?
            OR pos LIKE ?
            OR CAST(id AS CHAR) LIKE ?
         ORDER BY id DESC"
    );
    $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM faculty ORDER BY id DESC");
}

$reset_notice = $_SESSION['reset_notice'] ?? null;
if ($reset_notice) {
    unset($_SESSION['reset_notice']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty List</title>

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

/* CARD */
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

/* TABLE */
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
    margin-right:10px;
}

a.delete{
    color:#dc3545;
    text-decoration:none;
    font-weight:bold;
}

a.edit:hover,
a.reset:hover,
a.delete:hover{
    text-decoration:underline;
}

/* BACK */
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
    <h2>Faculty List</h2>

    <?php if ($reset_notice): ?>
        <div class="notice">
            Password reset for <b><?= h($reset_notice['email']) ?></b>. Temporary password: <b><?= h($reset_notice['password']) ?></b>
        </div>
    <?php endif; ?>

    <form class="search" method="get" action="view_faculty.php">
        <input type="text" name="q" placeholder="Search by name, email, position, or ID" value="<?= h($search) ?>">
        <button type="submit">Search</button>
        <?php if ($search !== ""): ?>
            <a class="clear" href="view_faculty.php">Clear</a>
        <?php endif; ?>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Position</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while($row = $result->fetch_assoc()) { ?>
            <?php
                $full_name = trim(($row['firstname'] ?? "") . " " . ($row['lastname'] ?? ""));
                $name_html = $full_name !== "" ? h($full_name) : "<i>Not Provided</i>";
                $return_page = "view_faculty.php";
                $return_q = $search !== "" ? "&q=" . urlencode($search) : "";
            ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= $name_html ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['pos']) ?></td>
                <td>
                    <a class="edit" href="update_faculty.php?id=<?= h($row['id']) ?>">Update</a>
                    <a class="reset" href="reset_faculty_password.php?id=<?= h($row['id']) ?>&return=<?= $return_page ?><?= $return_q ?>"
                       onclick="return confirm('Reset password for this faculty?')">
                       Reset Password
                    </a>
                    <a class="delete" href="delete_faculty.php?id=<?= h($row['id']) ?>"
                       onclick="return confirm('Delete this faculty?')">
                       Delete
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
