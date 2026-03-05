<?php
session_start();
ini_set('display_errors', 1); // لتشخيص أي مشكلة فوراً
error_reporting(E_ALL);

// الاتصال بقاعدة البيانات باستخدام متغيرات Railway
$conn = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT') ?: "3306");
$conn->set_charset("utf8mb4");

// 1. التثبيت التلقائي للجداول (المديرين، السحوبات)
$conn->query("CREATE TABLE IF NOT EXISTS admins (admin_id VARCHAR(100) PRIMARY KEY)");
$conn->query("INSERT IGNORE INTO admins (admin_id) VALUES ('1772506140')");

// 2. معالجة العمليات (حذف، موافقة) قبل عرض الصفحة
if (isset($_GET['approve_id'])) {
    $id = (int)$_GET['approve_id'];
    $conn->query("UPDATE withdraws SET status='COMPLETED' WHERE id=$id");
    header("Location: admin.php"); exit;
}
if (isset($_GET['delete_withdraw'])) {
    $id = (int)$_GET['delete_withdraw'];
    $conn->query("DELETE FROM withdraws WHERE id=$id");
    header("Location: admin.php"); exit;
}

// 3. منطق تسجيل الدخول
if (isset($_POST['login'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $res = $conn->query("SELECT * FROM admins WHERE admin_id = '$uid'");
    if ($res && $res->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $uid;
        header("Location: admin.php"); exit;
    } else { $error = "عذراً، الـ ID غير مسجل كمدير!"; }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

// حماية الصفحة: إذا لم يسجل دخول تظهر واجهة الدخول فقط
if (!isset($_SESSION['admin_logged'])) {
?>
<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>دخول الإدارة</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#0f172a; display:flex; align-items:center; justify-content:center; height:100vh; color:white;}</style></head>
<body><div class="card bg-dark border-secondary p-4 shadow-lg" style="width:350px;">
<h4 class="text-center mb-4"><i class="fas fa-user-lock"></i> بوابة المطور</h4>
<form method="POST"><input type="text" name="uid" class="form-control mb-3 bg-secondary text-white border-0" placeholder="أدخل معرف الـ ID" required>
<button name="login" class="btn btn-primary w-100 fw-bold">تسجيل الدخول</button></form>
<?php if(isset($error)) echo "<p class='text-danger mt-3 text-center small'>$error</p>"; ?>
</div></body></html>
<?php exit; } ?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BitView Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #f8fafc; padding-bottom: 80px; }
        .card { background: #1e293b; border: none; border-radius: 12px; margin-bottom: 15px; }
        .table { color: #cbd5e1; border-color: #334155; }
        .status-pending { color: #fbbf24; font-weight: bold; }
        .btn-approve { background: #10b981; border: none; color: white; }
    </style>
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 bg-primary p-3 rounded-3 shadow">
        <h5 class="mb-0"><i class="fas fa-terminal"></i> لوحة الإدارة الذكية</h5>
        <a href="?logout=1" class="btn btn-sm btn-light rounded-circle"><i class="fas fa-power-off text-danger"></i></a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6"><div class="card p-3 text-center text-info border-bottom border-info border-4">
            <h4 class="mb-0"><?= $conn->query("SELECT user_id FROM users")->num_rows ?></h4><small>إجمالي المستخدمين</small>
        </div></div>
        <div class="col-6"><div class="card p-3 text-center text-warning border-bottom border-warning border-4">
            <h4 class="mb-0"><?= $conn->query("SELECT id FROM withdraws WHERE status='PENDING'")->num_rows ?></h4><small>سحوبات معلقة</small>
        </div></div>
    </div>

    <div class="card p-3 shadow-sm">
        <h6 class="mb-3 text-warning"><i class="fab fa-paypal"></i> طلبات السحب المعلقة</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead><tr><th>الإيميل</th><th>المبلغ</th><th>إجراء</th></tr></thead>
                <tbody>
                    <?php 
                    $withdraws = $conn->query("SELECT * FROM withdraws WHERE status='PENDING' ORDER BY created_at DESC");
                    while($w = $withdraws->fetch_assoc()): ?>
                    <tr>
                        <td class="small"><?= $w['paypal_email'] ?></td>
                        <td class="fw-bold"><?= $w['amount'] ?>$</td>
                        <td>
                            <div class="btn-group">
                                <a href="?approve_id=<?= $w['id'] ?>" class="btn btn-sm btn-approve"><i class="fas fa-check"></i></a>
                                <a href="?delete_withdraw=<?= $w['id'] ?>" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3 mt-4 shadow-sm">
        <h6 class="mb-3 text-primary"><i class="fas fa-users"></i> جميع الأعضاء</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr class="text-secondary small"><th>الاسم</th><th>النقاط</th><th>التواصل</th></tr></thead>
                <tbody>
                    <?php $users = $conn->query("SELECT * FROM users LIMIT 20");
                    while($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="small fw-bold text-truncate" style="max-width: 80px;"><?= $u['user_name'] ?></td>
                        <td><span class="badge bg-dark text-success border border-success"><?= number_format($u['coins']) ?></span></td>
                        <td><a href="mailto:<?= $u['email'] ?>" class="text-info"><i class="fas fa-envelope fs-5"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3 mt-4 bg-dark">
        <form method="POST" class="input-group input-group-sm">
            <input type="text" name="new_adm" class="form-control bg-secondary text-white border-0" placeholder="إضافة ID إداري جديد">
            <button class="btn btn-success" name="add_adm">إضافة</button>
        </form>
    </div>
    <?php if(isset($_POST['add_adm'])){ $na = $conn->real_escape_string($_POST['new_adm']); $conn->query("INSERT IGNORE INTO admins VALUES ('$na')"); echo "<script>location.href='admin.php';</script>"; } ?>

</body>
</html>
