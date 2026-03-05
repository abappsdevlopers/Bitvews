<?php
session_start();

// 1. الاتصال الذكي بقاعدة البيانات
$host = !empty(getenv('MYSQLHOST')) ? getenv('MYSQLHOST') : 'localhost';
$user = !empty(getenv('MYSQLUSER')) ? getenv('MYSQLUSER') : 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'bitview';
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) { die("خطأ اتصال: " . $conn->connect_error); }

// 2. تهيئة الجداول التلقائية
$conn->query("CREATE TABLE IF NOT EXISTS admins (admin_id VARCHAR(100) PRIMARY KEY)");
$conn->query("INSERT IGNORE INTO admins (admin_id) VALUES ('1772506140')");

// 3. معالجة العمليات (تسجيل الدخول، حذف، قبول)
if (isset($_POST['login'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res && $res->num_rows > 0) { $_SESSION['admin_logged'] = true; header("Location: " . $_SERVER['PHP_SELF']); exit; }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: " . $_SERVER['PHP_SELF']); exit; }
if (isset($_GET['approve'])) { $conn->query("UPDATE withdraws SET status='COMPLETED' WHERE id=".(int)$_GET['approve']); }
if (isset($_GET['delete'])) { $conn->query("DELETE FROM withdraws WHERE id=".(int)$_GET['delete']); }

// 4. الحماية
if (!isset($_SESSION['admin_logged'])) {
    echo '<!DOCTYPE html><html dir="rtl"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-dark d-flex align-items-center justify-content-center" style="height:100vh;">
    <form method="POST" class="card p-4 shadow" style="width:320px;">
        <h4 class="text-center text-primary">دخول الإدارة</h4>
        <input type="text" name="uid" class="form-control mb-3" placeholder="أدخل ID المدير" required>
        <button name="login" class="btn btn-primary w-100">دخول</button>
    </form></body></html>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم BitView</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #121418; color: #fff; }
        .main-card { background: #1d232a; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .table { color: #fff; }
    </style>
</head>
<body class="p-4">
    <div class="d-flex justify-content-between mb-4">
        <h4><i class="fas fa-rocket text-info"></i> BitView Panel</h4>
        <a href="?logout=1" class="btn btn-danger btn-sm">خروج</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6"><div class="main-card text-center"><h6><?=$conn->query("SELECT id FROM users")->num_rows?></h6><small>المستخدمين</small></div></div>
        <div class="col-6"><div class="main-card text-center border-warning"><h6><?=$conn->query("SELECT id FROM withdraws WHERE status='PENDING'")->num_rows?></h6><small>طلبات معلقة</small></div></div>
    </div>

    <div class="main-card mb-4">
        <h6><i class="fab fa-paypal text-info"></i> طلبات السحب</h6>
        <table class="table table-hover table-sm">
            <thead><tr><th>البريد</th><th>المبلغ</th><th>إجراء</th></tr></thead>
            <tbody>
                <?php $res = $conn->query("SELECT * FROM withdraws WHERE status='PENDING'");
                while($row = $res->fetch_assoc()): ?>
                <tr><td><?=$row['paypal_email']?></td><td><?=$row['amount']?>$</td>
                <td>
                    <a href="?approve=<?=$row['id']?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                    <a href="?delete=<?=$row['id']?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                </td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="main-card">
        <h6><i class="fas fa-users text-primary"></i> المستخدمين</h6>
        <table class="table table-sm">
            <thead><tr><th>المستخدم</th><th>النقاط</th></tr></thead>
            <tbody>
                <?php $res = $conn->query("SELECT user_name, coins FROM users LIMIT 10");
                while($u = $res->fetch_assoc()): ?>
                <tr><td><?=$u['user_name']?></td><td><span class="badge bg-primary"><?=$u['coins']?></span></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
