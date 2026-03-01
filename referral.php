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

$conn->set_charset("utf8mb4");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$referrer_id = $data['referrer_id'] ?? null;
$reward = (int)($data['reward_amount'] ?? 0);

if ($referrer_id && $reward > 0) {
    // 1. تنظيف المعرف المرسل من أي مسافات أو أسطر جديدة مخفية
    $referrer_id = preg_replace('/\s+/', '', $referrer_id);

    // 2. تحديث الرصيد مع تنظيف العمود في قاعدة البيانات أثناء البحث لضمان التطابق
    // نستخدم TRIM لحذف المسافات من الجانبين في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE users SET coins = coins + ? WHERE TRIM(user_id) = ?");
    $stmt->bind_param("is", $reward, $referrer_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status" => "success", 
            "message" => "Reward granted to $referrer_id"
        ]);
    } else {
        // إذا فشل التحديث، نقوم بعمل استعلام فحص (Debug) لنرى لماذا لا يجد المعرف
        $check = $conn->prepare("SELECT user_id FROM users WHERE user_id LIKE ?");
        $search_like = "%" . substr($referrer_id, -5) . "%"; // البحث بآخر 5 أرقام للتأكد
        $check->bind_param("s", $search_like);
        $check->execute();
        $res = $check->get_result();
        
        $found_similar = [];
        while($row = $res->fetch_assoc()) { $found_similar[] = $row['user_id']; }

        echo json_encode([
            "status" => "error", 
            "message" => "Strict match failed",
            "sent_id" => $referrer_id,
            "similar_ids_in_db" => $found_similar // سيظهر لك هنا كيف هو مخزن فعلياً في القاعدة
        ]);
        $check->close();
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
$conn->close();
?>
