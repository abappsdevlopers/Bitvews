<?php
// إعدادات الاتصال (كما هي)
$conn = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT'));

// إذا طلب Godot البيانات بصيغة JSON
if (isset($_GET['action']) && $_GET['action'] == 'get_data') {
    header('Content-Type: application/json');
    
    $users = $conn->query("SELECT count(*) as count FROM users")->fetch_assoc()['count'];
    $withdrawals = $conn->query("SELECT * FROM withdraws WHERE status='PENDING'")->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'users' => $users,
        'pending' => count($withdrawals),
        'withdrawals' => $withdrawals
    ]);
    exit;
}
?>
