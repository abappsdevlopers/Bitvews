<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// الاتصال المباشر
$conn = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT') ?: "3306");
if ($conn->connect_error) { die("خطأ اتصال: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

// 1. التأكد من وجود جدول المديرين فقط
$conn->query("CREATE TABLE IF NOT EXISTS admins (admin_id VARCHAR(100) PRIMARY KEY)");
$conn->query("INSERT IGNORE INTO admins (admin_id) VALUES ('1772506140')");

// 2. منطق تسجيل الدخول
if (isset($_POST['uid'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res && $res->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
    }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

// 3. التحقق
if (!isset($_SESSION['admin_logged'])) {
    echo '<!DOCTYPE html><html dir="rtl"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-dark p-5"><form method="POST" class="card p-3 mx-auto" style="max-width:300px;"><input type="text" name="uid" class="form-control mb-2" placeholder="ID" required><button class="btn btn-primary w-100">دخول</button></form></body></html>';
    exit;
}

// 4. العمليات (قبول/حذف)
if (isset($_GET['approve'])) { $conn->query("UPDATE withdraws SET status='COMPLETED' WHERE id=".(int)$_GET['approve']); header("Location: admin.php"); }
if (isset($_GET['delete'])) { $conn->query("DELETE FROM withdraws WHERE id=".(int)$_GET['delete']); header("Location: admin.php"); }
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{background:#0f172a; color:#fff;}</style>
</head>
<body class="p-3">
    <div class="d-flex justify-content-between mb-3"><h4>لوحة التحكم</h4><a href="?logout=1" class="btn btn-danger btn-sm">خروج</a></div>
    
    <div class="card bg-dark text-white p-3">
        <h6>طلبات السحب المعلقة</h6>
        <table class="table table-dark table-sm">
            <?php $res = $conn->query("SELECT * FROM withdraws WHERE status='PENDING'");
            while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?=$row['paypal_email']?></td>
                <td><?=$row['amount']?>$</td>
                <td>
                    <a href="?approve=<?=$row['id']?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                    <a href="?delete=<?=$row['id']?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
