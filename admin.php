<?php
session_start();
ini_set('display_errors', 0); // إخفاء الأخطاء في الواجهة للجماليات

// الاتصال بقاعدة البيانات
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) { die("خطأ في الاتصال بالسيرفر!"); }
$conn->set_charset("utf8mb4");

// تسجيل الدخول
if (isset($_POST['login'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res && $res->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $uid;
        header("Location: admin.php"); exit;
    }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

// حماية الصفحة
if (!isset($_SESSION['admin_logged'])) {
    echo '<!DOCTYPE html><html dir="rtl"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-dark d-flex align-items-center justify-content-center" style="height:100vh;"><form method="POST" class="card p-4" style="width:300px;">
    <h4 class="text-center">بوابة الإدارة</h4><input type="text" name="uid" class="form-control mb-3" placeholder="ID المدير" required>
    <button class="btn btn-primary w-100">دخول</button></form></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar"><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{background:#0f172a; color:#fff;} .card{background:#1e293b; border-radius:15px;}</style>
</head>
<body class="p-3">
    <div class="d-flex justify-content-between mb-4">
        <h5>لوحة تحكم BitView</h5>
        <a href="?logout=1" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6"><div class="card p-3 text-center"><h6><?=$conn->query("SELECT user_id FROM users")->num_rows?></h6><small>مستخدم</small></div></div>
        <div class="col-6"><div class="card p-3 text-center"><h6><?=$conn->query("SELECT id FROM withdraws WHERE status='PENDING'")->num_rows?></h6><small>سحب معلق</small></div></div>
    </div>

    <div class="card p-3">
        <table class="table table-dark table-hover table-sm">
            <thead><tr><th>الإيميل</th><th>المبلغ</th><th>تحكم</th></tr></thead>
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

    <?php 
    // عمليات التحديث
    if(isset($_GET['approve'])){ $conn->query("UPDATE withdraws SET status='COMPLETED' WHERE id=".(int)$_GET['approve']); echo "<script>location.href='admin.php';</script>"; }
    if(isset($_GET['delete'])){ $conn->query("DELETE FROM withdraws WHERE id=".(int)$_GET['delete']); echo "<script>location.href='admin.php';</script>"; }
    ?>
</body>
</html>
