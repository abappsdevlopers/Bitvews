<?php
header("Content-Type: application/json");

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

try {
    // الاتصال باستخدام التنسيق المباشر
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['referrer_id'])) {
        $ref_id = preg_replace('/\s+/', '', $data['referrer_id']);
        $amount = (int)$data['reward_amount'];

        $sql = "UPDATE users SET coins = coins + :amt WHERE user_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':amt' => $amount, ':id' => $ref_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Referrer rewarded"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Referrer ID not found"]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Driver/DB Error: " . $e->getMessage()]);
}
