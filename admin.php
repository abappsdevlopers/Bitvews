<?php
// إعدادات الاتصال (كما هي)
$conn = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT'));

// إضافة نقطة نهاية للـ API لجلب البيانات لـ Godot
if (isset($_GET['action']) && $_GET['action'] == 'get_data') {
    header('Content-Type: application/json');
    
    // جلب الإحصائيات
    $users_count = $conn->query("SELECT id FROM users")->num_rows;
    $pending_count = $conn->query("SELECT id FROM withdraws WHERE status='PENDING'")->num_rows;
    
    // جلب قائمة السحوبات
    $withdraws = $conn->query("SELECT * FROM withdraws WHERE status='PENDING'")->fetch_all(MYSQLI_ASSOC);
    
    // إرسال البيانات بصيغة JSON
    echo json_encode([
        'users' => $users_count,
        'pending' => $pending_count,
        'withdrawals' => $withdraws
    ]);
    exit; // التوقف هنا لمنع تحميل HTML
}
?>
