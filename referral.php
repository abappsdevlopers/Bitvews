<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed"]));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$referrer_id = $data['referrer_id'] ?? null;
$reward = (int)($data['reward_amount'] ?? 0);

if ($referrer_id && $reward > 0) {
    // 1. تنظيف المعرف المرسل من Godot
    $clean_id = trim($referrer_id);

    // 2. البحث والتحديث باستخدام LIKE و % لضمان العثور عليه حتى لو بجانبه مسافات مخفية
    // هذا الاستعلام سيحدث الرصيد لأي مستخدم يحتوي معرفه على هذا الرقم بالضبط
    $sql = "UPDATE users SET coins = coins + $reward WHERE user_id LIKE '%$clean_id%'";
    
    $conn->query($sql);

    if ($conn->affected_rows > 0) {
        echo json_encode([
            "status" => "success", 
            "message" => "Final Success! Reward added to $clean_id"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Even with flexible search, ID not found",
            "sent_id" => $clean_id
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No data"]);
}
$conn->close();
?>
