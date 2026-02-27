<?php
// منع ظهور الأخطاء وتفعيل التخزين المؤقت للمخرجات
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

// 1. إعدادات البيئة
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass_db = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// 2. تنظيف قسري للمخرجات لإزالة أي مسافات مخفية أو أخطاء
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli($host, $user, $pass_db, $db, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection Failed"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email_input = isset($input['email']) ? trim($input['email']) : '';

if (!empty($email_input)) {
    // استخدام أسماء الأعمدة الخاصة بك: email و pass
    $stmt = $conn->prepare("SELECT pass FROM users WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // إرسال البيانات (تأكد أن جودو يقرأ هذه المفاتيح)
        echo json_encode([
            "status" => "success", 
            "user_email" => $email_input,
            "user_pass" => $row['pass']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Email field is empty"]);
}

$conn->close();
exit; 
?>
