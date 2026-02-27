<?php
// 1. منع أي أخطاء PHP من الظهور وإفساد الـ JSON
error_reporting(0);
ob_start();

// 2. إعدادات البيئة من ريلواي
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass_db = getenv('MYSQLPASSWORD'); // استخدمت اسم مختلف للمتغير لتجنب الخلط مع كلمة مرور المستخدم
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// 3. تنظيف أي مخرجات غريبة قبل إرسال الهيدر
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 4. الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass_db, $db, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection Failed"]);
    exit;
}

// 5. استقبال البيانات من Godot
$input = json_decode(file_get_contents('php://input'), true);
$email_input = isset($input['email']) ? trim($input['input']) : (isset($input['email']) ? trim($input['email']) : '');

// تصحيح بسيط لضمان التقاط الإيميل أياً كان مسمى المفتاح
if (empty($email_input)) {
    $email_input = $input['email'] ?? '';
}

if (!empty($email_input)) {
    // 6. استخدام أسماء الأعمدة الخاصة بك (email و pass)
    $stmt = $conn->prepare("SELECT pass FROM users WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // 7. إرسال البيانات بالمسميات التي يتوقعها كود Godot الجديد
        echo json_encode([
            "status" => "success", 
            "user_email" => $email_input,
            "user_pass" => $row['pass'] // استخدام 'pass' من قاعدة البيانات
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found in database"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Email field is empty"]);
}

$conn->close();
exit; 
?>
