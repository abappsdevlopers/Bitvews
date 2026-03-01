<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed"]));
}

// ضبط التشفير لضمان قراءة الرموز مثل @ بشكل صحيح
$conn->set_charset("utf8mb4");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$referrer_id = $data['referrer_id'] ?? null;
$reward = (int)($data['reward_amount'] ?? 0);

if ($referrer_id && $reward > 0) {
    // تنظيف المعرف من أي مسافات مخفية
    $referrer_id = trim($referrer_id);

    // استخدام Prepared Statement لضمان مطابقة النص 100%
    $stmt = $conn->prepare("UPDATE users SET coins = coins + ? WHERE user_id = ?");
    $stmt->bind_param("is", $reward, $referrer_id); // i للرقم، s للنص
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status" => "success", 
            "message" => "Reward added to $referrer_id"
        ]);
    } else {
        // إذا وصلنا هنا، يعني السيرفر لم يجد تطابقاً بين النص المرسل والقاعدة
        echo json_encode([
            "status" => "error", 
            "message" => "No match found for ID",
            "sent_id" => $referrer_id
        ]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}

$conn->close();
?>
