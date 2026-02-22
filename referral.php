<?php
header("Content-Type: application/json");

// 1. جلب معلومات قاعدة البيانات من متغيرات بيئة Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

try {
    // 2. الاتصال بقاعدة البيانات باستخدام PDO
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. استقبال البيانات القادمة من تطبيق Godot
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data && isset($data['referrer_id'])) {
        $referrer_id = $data['referrer_id'];
        $reward = (int)$data['reward_amount'];

        // 4. تحديث رصيد الشخص الذي شارك الرابط (الداعي)
        // تأكد أن أسماء الأعمدة (coins, user_id) تطابق جدولك بالضبط
        $sql = "UPDATE users SET coins = coins + ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reward, $referrer_id]);

        // التحقق مما إذا كان المعرف موجوداً فعلياً وتم التحديث
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Reward added to referrer: " . $referrer_id
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
            "message" => "Invalid JSON or missing referrer_id"
        ]);
    }

} catch (PDOException $e) {
    // في حال فشل الاتصال بقاعدة البيانات
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
}
?>
