<?php
session_start();
// إعدادات الاتصال بقاعدة البيانات
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

// معالجة تسجيل الدخول
if (isset($_POST['login'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res && $res->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $uid;
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: " . $_SERVER['PHP_SELF']); exit; }

// حماية الصفحة
if (!isset($_SESSION['admin_logged'])) {
    echo '<!DOCTYPE html><html dir="rtl"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-dark d-flex align-items-center justify-content-center" style="height:100vh;">
    <form method="POST" class="card p-4 shadow" style="width:320px;">
        <h4 class="text-center mb-3 text-primary">تسجيل دخول الإدارة</h4>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم BitView</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #121418; color: #fff; }
        .card { background: #1d232a; border-radius: 15px; }
        .table { color: #fff; }
    </style>
</head>
<body class="p-4">
    <div class="d-flex justify-content-between mb-4">
        <h4><i class="fas fa-shield-halved text-info"></i> لوحة الإدارة</h4>
        <a href="?logout=1" class="btn btn-danger btn-sm">خروج</a>
    </div>

    <div class="card p-3 mb-4">
        <form method="POST" class="row g-2 align-items-center">
            <div class="col-auto"><h6>إضافة مدير:</h6></div>
            <div class="col"><input type="text" name="new_adm" class="form-control" placeholder="ID المدير الجديد"></div>
            <div class="col-auto"><button name="add_adm" class="btn btn-success">إضافة</button></div>
        </form>
    </div>

    <?php 
    if(isset($_POST['add_adm'])){ 
        $na = $conn->real_escape_string($_POST['new_adm']);
        $conn->query("INSERT IGNORE INTO admins (admin_id) VALUES ('$na')");
    }
    ?>

    <div class="card p-3">
        <h6 class="mb-3">قائمة المستخدمين</h6>
        <table class="table table-hover table-sm">
            <thead><tr><th>الاسم</th><th>النقاط</th></tr></thead>
            <tbody>
                <?php $users = $conn->query("SELECT user_name, coins FROM users LIMIT 10");
                while($u = $users->fetch_assoc()): ?>
                <tr><td><?=$u['user_name']?></td><td><?=$u['coins']?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
