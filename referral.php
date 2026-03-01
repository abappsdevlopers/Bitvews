<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. الاتصال بنفس الطريقة المضمونة في ملف الحفظ
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

// 2. قراءة البيانات
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$referrer_id = $data['referrer_id'] ?? null;
$reward = (int)($data['reward_amount'] ?? 0);

if ($referrer_id && $reward > 0) {
    $referrer_id = trim($referrer_id);

    // 3. محاولة التحديث
    $sql = "UPDATE users SET coins = coins + $reward WHERE user_id = '$referrer_id'";
    $conn->query($sql);

    if ($conn->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Reward granted!"]);
    } else {
        // 4. إذا فشل، لنعرف ما هي المعرفات الموجودة فعلياً في جدولك؟
        $all_users = [];
        $result = $conn->query("SELECT user_id FROM users LIMIT 10"); // جلب أول 10 مستخدمين للفحص
        while($row = $result->fetch_assoc()) {
            $all_users[] = $row['user_id'];
        }

        echo json_encode([
            "status" => "error",
            "message" => "ID not found in this specific database",
            "you_sent" => $referrer_id,
            "real_ids_in_db_now" => $all_users, // هنا سنرى الحقيقة
            "total_users_count" => $conn->query("SELECT count(*) as total FROM users")->fetch_assoc()['total']
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No data"]);
}
$conn->close();
?>
