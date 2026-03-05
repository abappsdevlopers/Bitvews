<?php
// 1. تفعيل إظهار الأخطاء (مؤقتاً لحل مشكلة 500)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. جلب المتغيرات من Railway
$host   = getenv('MYSQLHOST') ?: 'localhost';
$user   = getenv('MYSQLUSER');
$pass   = getenv('MYSQLPASSWORD');
$port   = getenv('MYSQLPORT') ?: "3306";
$dbname = getenv('MYSQLDATABASE');

// 3. محاولة الاتصال مع معالجة الأخطاء
try {
    // في ريلوي، أحياناً نحتاج لتمرير المنفذ (Port) كمعامل خامس
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    
    // التحقق من وجود خطأ في الاتصال
    if ($conn->connect_error) {
        throw new Exception("فشل الاتصال: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // سيطبع لك الخطأ الحقيقي بدلاً من صفحة 500 البيضاء
    die("<div style='color:red; background:#fff; padding:20px;'>
            <h3>خطأ في قاعدة البيانات:</h3>" . $e->getMessage() . "
         </div>");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BitView Admin | لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #0f172a; color: #f8fafc; padding-bottom: 70px; }
        .card { background-color: #1e293b; border: none; border-radius: 12px; }
        .search-box { background-color: #334155; border: none; color: white; border-radius: 25px; }
        .search-box:focus { background-color: #475569; color: white; box-shadow: none; }
        .table { color: #cbd5e1; }
        .table-hover tbody tr:hover { background-color: #334155; transition: 0.3s; }
        .navbar-custom { background: linear-gradient(90deg, #1e40af, #3b82f6); }
        .badge-points { background-color: #10b981; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom shadow-sm mb-4">
    <div class="container">
        <span class="navbar-brand"><i class="fas fa-gem me-2"></i> BitView Admin</span>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form action="" method="GET" class="input-group">
                <input type="text" name="search" class="form-control search-box px-4" placeholder="ابحث بالإيميل أو اسم المستخدم..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button class="btn btn-primary rounded-pill ms-2" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة الأعضاء (<?php echo $result->num_rows; ?>)</h5>
            <a href="admin.php" class="btn btn-sm btn-outline-info text-decoration-none">إظهار الكل</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-4">المستخدم</th>
                            <th>النقاط</th>
                            <th>التاريخ</th>
                            <th class="text-center">تواصل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold"><?php echo $row['username']; ?></div>
                                    <div class="small text-muted"><?php echo $row['email']; ?></div>
                                </td>
                                <td><span class="badge badge-points"><?php echo number_format($row['points']); ?></span></td>
                                <td><?php echo date('M d', strtotime($row['created_at'])); ?></td>
                                <td class="text-center">
                                    <a href="mailto:<?php echo $row['email']; ?>" class="btn btn-sm btn-info rounded-circle text-white">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4">لم يتم العثور على نتائج!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="d-md-none fixed-bottom bg-dark border-top border-secondary p-2 d-flex justify-content-around">
    <a href="admin.php" class="text-info text-decoration-none text-center">
        <i class="fas fa-home d-block fs-5"></i><small>الرئيسية</small>
    </a>
    <a href="#" class="text-muted text-decoration-none text-center">
        <i class="fas fa-chart-line d-block fs-5"></i><small>إحصائيات</small>
    </a>
</div>

</body>
</html>
<?php $conn->close(); ?>
