<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// جلب البيانات تلقائياً من Railway (نفس متغيرات ملف الحفظ)
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

// الاتصال باستخدام mysqli لضمان عمل الـ Driver
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// استقبال البيانات من Godot
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data && isset($data['referrer_id'])) {
    // تنظيف البيانات لمنع الثغرات والمسافات الزائدة
    $referrer_id = $conn->real_escape_string(trim($data['referrer_id']));
    $reward = (int)$data['reward_amount'];

    // التأكد من أن المعرف ليس فارغاً
    if (!empty($referrer_id) && $reward > 0) {
        
        // تحديث العملات للمشارك (الداعي)
        // استخدمنا نفس اسم العمود user_id من ملف الحفظ الخاص بك
        $sql = "UPDATE users SET coins = coins + $reward WHERE user_id = '$referrer_id'";

        if ($conn->query($sql) === TRUE) {
            // التحقق هل تم العثور على الصف فعلياً؟
            if ($conn->affected_rows > 0) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Reward of $reward added to $referrer_id"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Referrer ID not found in database"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Query error: " . $conn->error
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid ID or Reward"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No data received"]);
}

$conn->close();
?>
