<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// جلب البيانات من Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $uid = $conn->real_escape_string($data['user_id']);
    $upass = $conn->real_escape_string($data['pass']);

    // التحقق من وجود المستخدم وكلمة المرور قبل الحذف
    $check = $conn->query("SELECT * FROM users WHERE user_id = '$uid' AND pass = '$upass'");

    if ($check && $check->num_rows > 0) {
        // تنفيذ أمر الحذف
        $sql = "DELETE FROM users WHERE user_id = '$uid'";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "ﺡﺎﺠﻨﺑ ﺏﺎﺴﺤﻟﺍ ﻑﺬﺣ ﻢﺗ" // "تم حذف الحساب بنجاح"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "error", 
            "message" => "ﺔﺌﻃﺎﺧ ﺭﻭﺭﻤﻟﺍ ﺔﻤﻠﻛ" // "كلمة المرور خاطئة"
        ]);
    }
} else {
    echo json_encode(["error" => "No data received"]);
}

$conn->close();
?>
