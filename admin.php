<?php
session_start();
ini_set('display_errors', 0);

// === إعدادات قاعدة البيانات ===
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("خطأ في الاتصال بالسيرفر!");
}
$conn->set_charset("utf8mb4");

// === دوال مساعدة ===
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function validateAdminID($uid) {
    // التحقق من أن الـ ID يحتوي على أحرف وأرقام فقط
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $uid) ? true : false;
}

// === معالجة Logout ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// === معالجة Login ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // التحقق من CSRF Token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $login_error = "طلب غير آمن!";
    } else {
        $uid = trim($_POST['uid'] ?? '');
        
        // التحقق من صحة الـ ID
        if (empty($uid)) {
            $login_error = "الرجاء إدخال ID المدير";
        } elseif (!validateAdminID($uid)) {
            $login_error = "صيغة ID غير صحيحة";
        } else {
            // استخدام Prepared Statement لمنع SQL Injection
            $stmt = $conn->prepare("SELECT admin_id, password_hash FROM admins WHERE admin_id = ? LIMIT 1");
            $stmt->bind_param("s", $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                
                // التحقق من كلمة المرور (إذا كانت موجودة)
                $password = trim($_POST['password'] ?? '');
                if (!empty($password) && !password_verify($password, $row['password_hash'])) {
                    $login_error = "بيانات دخول خاطئة";
                } else {
                    // تحديث معرف Session لتجنب Session Fixation
                    session_regenerate_id(true);
                    
                    $_SESSION['admin_logged'] = true;
                    $_SESSION['admin_id'] = $row['admin_id'];
                    $_SESSION['login_time'] = time();
                    
                    header("Location: admin.php");
                    exit;
                }
            } else {
                $login_error = "بيانات دخول خاطئة";
            }
            $stmt->close();
        }
    }
}

// === حماية الصفحة ===
if (!isset($_SESSION['admin_logged']) || !isset($_SESSION['login_time'])) {
    $csrf_token = generateCSRFToken();
    echo '<!DOCTYPE html>
    <html dir="rtl" lang="ar">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>بوابة الإدارة</title>
    </head>
    <body class="bg-dark d-flex align-items-center justify-content-center" style="height:100vh;">
        <form method="POST" class="card p-4" style="width:350px; background:#1e293b;">
            <h4 class="text-center mb-4 text-light">بوابة الإدارة</h4>
            ' . (isset($login_error) ? '<div class="alert alert-danger alert-sm" role="alert">' . htmlspecialchars($login_error) . '</div>' : '') . '
            <input type="text" name="uid" class="form-control mb-3" placeholder="ID المدير" required maxlength="50">
            <input type="password" name="password" class="form-control mb-3" placeholder="كلمة المرور" required>
            <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token) . '">
            <button type="submit" name="login" class="btn btn-primary w-100">دخول</button>
        </form>
    </body>
    </html>';
    exit;
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>لوحة التحكم</title>
    <style>
        body { background:#0f172a; color:#fff; }
        .card { background:#1e293b; border-radius:15px; }
        .stat-card { transition: transform 0.3s; }
        .stat-card:hover { transform: scale(1.05); }
    </style>
</head>
<body class="p-3">
    <div class="d-flex justify-content-between mb-4">
        <h5><i class="fas fa-tachometer-alt"></i> لوحة تحكم BitView</h5>
        <div>
            <span class="me-3 text-muted">مرحباً: <strong><?=htmlspecialchars($_SESSION['admin_id'])?></strong></span>
            <a href="?logout=1" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> خروج</a>
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6">
            <div class="card p-3 text-center stat-card">
                <h6 class="text-warning"><?php 
                    $res = $conn->query("SELECT COUNT(*) as count FROM users");
                    echo $res ? $res->fetch_assoc()['count'] : 0;
                ?></h6>
                <small class="text-muted">مستخدم</small>
            </div>
        </div>
        <div class="col-6">
            <div class="card p-3 text-center stat-card">
                <h6 class="text-danger"><?php 
                    $res = $conn->query("SELECT COUNT(*) as count FROM withdraws WHERE status='PENDING'");
                    echo $res ? $res->fetch_assoc()['count'] : 0;
                ?></h6>
                <small class="text-muted">سحب معلق</small>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <h6 class="mb-3"><i class="fas fa-list"></i> قائمة طلبات السحب المعلقة</h6>
        <div class="table-responsive">
            <table class="table table-dark table-hover table-sm mb-0">
                <thead>
                    <tr class="table-secondary">
                        <th>الإيميل</th>
                        <th>المبلغ</th>
                        <th>تاريخ الطلب</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res = $conn->query("SELECT id, paypal_email, amount, created_at FROM withdraws WHERE status='PENDING' ORDER BY created_at DESC");
                    if ($res && $res->num_rows > 0):
                        while($row = $res->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?=htmlspecialchars($row['paypal_email'])?></td>
                        <td><span class="badge bg-success"><?=htmlspecialchars($row['amount'])?>$</span></td>
                        <td><?=htmlspecialchars($row['created_at'] ?? 'غير محدد')?></td>
                        <td>
                            <a href="?action=approve&id=<?=htmlspecialchars($row['id'])?>&csrf=<?=htmlspecialchars($_SESSION['csrf_token'])?>" class="btn btn-success btn-sm" onclick="return confirm('هل تأكد من الموافقة؟')">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="?action=delete&id=<?=htmlspecialchars($row['id'])?>&csrf=<?=htmlspecialchars($_SESSION['csrf_token'])?>" class="btn btn-danger btn-sm" onclick="return confirm('هل تأكد من الحذف؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">لا توجد طلبات معلقة</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    // === معالجة العمليات ===
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $withdraw_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $csrf = isset($_GET['csrf']) ? $_GET['csrf'] : '';

    // التحقق من CSRF Token
    if (!verifyCSRFToken($csrf)) {
        die("خطأ أمني: طلب غير موثوق!");
    }

    if ($action === 'approve' && $withdraw_id > 0) {
        $stmt = $conn->prepare("UPDATE withdraws SET status='COMPLETED', completed_at=NOW() WHERE id=? AND status='PENDING'");
        $stmt->bind_param("i", $withdraw_id);
        if ($stmt->execute()) {
            echo "<script>alert('تم الموافقة بنجاح'); location.href='admin.php';</script>";
        }
        $stmt->close();
    }
    elseif ($action === 'delete' && $withdraw_id > 0) {
        $stmt = $conn->prepare("DELETE FROM withdraws WHERE id=? AND status='PENDING'");
        $stmt->bind_param("i", $withdraw_id);
        if ($stmt->execute()) {
            echo "<script>alert('تم الحذف بنجاح'); location.href='admin.php';</script>";
        }
        $stmt->close();
    }
    ?>

</body>
</html>
