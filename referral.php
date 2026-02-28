<?php
header("Content-Type: application/json");

// 1. إعدادات قاعدة البيانات من Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. استقبال البيانات (دعم JSON و POST العادي)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // إذا لم يكن JSON، نحاول جلبها من $_POST (للتوافق مع بعض طرق Godot)
    $referrer_id = $data['referrer_id'] ?? $_POST['referrer_id'] ?? null;
    $reward = (int)($data['reward_amount'] ?? $_POST['reward_amount'] ?? 0);

    if ($referrer_id && $reward > 0) {
        
        // ملاحظة هامة: تأكد أن اسم العمود في جدولك هو 'id' أو 'user_id' 
        // سأستخدم 'user_id' بناءً على كودك السابق
        $sql = "UPDATE users SET coins = coins + :reward WHERE user_id = :ref_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reward' => $reward,
            ':ref_id' => $referrer_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "تم إضافة $reward نقطة للمشارك رقم: $referrer_id"
            ]);
        } else {
            // سجل الخطأ في قاعدة البيانات أو استجب بأن المعرف غير موجود
            echo json_encode([
                "status" => "error",
                "message" => "لم يتم العثور على معرف المشارك في قاعدة البيانات أو لم يتغير الرصيد"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "بيانات غير مكتملة. المعرف: $referrer_id ، القيمة: $reward"
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "فشل الاتصال: " . $e->getMessage()
    ]);
}
?>
