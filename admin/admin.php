<?php
session_start();
// الاتصال بقاعدة البيانات
$conn = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT') ?: "3306");
$conn->set_charset("utf8mb4");

// 1. التثبيت التلقائي للجداول
$conn->query("CREATE TABLE IF NOT EXISTS admins (admin_id VARCHAR(100) PRIMARY KEY)");
$conn->query("INSERT IGNORE INTO admins (admin_id) VALUES ('1772506140')");

// 2. منطق تسجيل الدخول
if (isset($_POST['login'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $uid;
        header("Location: admin.php"); exit;
    } else { $error = "غير مصرح لك بالدخول!"; }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

// حماية الصفحة
if (!isset($_SESSION['admin_logged'])) {
    echo '<!DOCTYPE html><html dir="rtl"><head><title>BitView Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-dark text-light"><div class="container mt-5"><div class="card bg-secondary p-4 mx-auto" style="max-width:350px;">
    <h3 class="text-center"><i class="fas fa-lock"></i> دخول الإدارة</h3>
    <form method="POST"><input type="text" name="uid" class="form-control mb-3" placeholder="أدخل ID الإداري" required>
    <button name="login" class="btn btn-primary w-100">دخول</button></form><p class="text-danger mt-2 text-center">'.$error.'</p></div></div></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BitView Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #f8fafc; }
        .card { background: #1e293b; border-radius: 15px; }
        .nav-link { color: #94a3b8; }
    </style>
</head>
<body class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-primary"><i class="fas fa-shield-alt"></i> BitView Admin</h4>
        <a href="?logout=1" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6"><div class="card p-3 text-center"><h5><?=$conn->query("SELECT * FROM users")->num_rows?></h5><small>مستخدم</small></div></div>
        <div class="col-6"><div class="card p-3 text-center"><h5><?=$conn->query("SELECT * FROM withdraws WHERE status='PENDING'")->num_rows?></h5><small>سحب معلق</small></div></div>
    </div>

    <div class="card p-3 table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead><tr><th>المستخدم</th><th>النقاط</th><th>الإيميل</th></tr></thead>
            <tbody>
                <?php $users = $conn->query("SELECT * FROM users");
                while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><i class="fas fa-user-circle me-2 text-primary"></i><?=$u['user_name']?></td>
                    <td><span class="badge bg-success"><?=$u['coins']?></span></td>
                    <td><?=$u['email']?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-3 mt-4">
        <form method="POST" class="input-group">
            <input type="text" name="new_admin" class="form-control" placeholder="ID إداري جديد...">
            <button class="btn btn-success" name="add_admin"><i class="fas fa-plus"></i></button>
        </form>
    </div>
    
    <?php if(isset($_POST['add_admin'])){ $conn->query("INSERT IGNORE INTO admins VALUES('".$_POST['new_admin']."')"); } ?>
</body>
</html>
