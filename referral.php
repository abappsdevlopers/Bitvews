<?php
header("Content-Type: application/json");

// 1. Database Configuration
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

    // 2. Receiving Data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $referrer_id = $data['referrer_id'] ?? $_POST['referrer_id'] ?? null;
    $reward = (int)($data['reward_amount'] ?? $_POST['reward_amount'] ?? 0);

    // --- IMPORTANT: Clean the ID from any invisible characters ---
    if ($referrer_id) {
        // Remove spaces, tabs, and newlines
        $referrer_id = preg_replace('/\s+/', '', $referrer_id); 
    }

    if ($referrer_id && $reward > 0) {
        
        // 3. Update Query
        $sql = "UPDATE users SET coins = coins + :reward WHERE user_id = :ref_id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue(':reward', $reward, PDO::PARAM_INT);
        $stmt->bindValue(':ref_id', $referrer_id, PDO::PARAM_STR);
        $stmt->execute();

        // Check if the update actually happened
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Reward added successfully to " . $referrer_id
            ]);
        } else {
            // Logic Check: Maybe the ID exists but coins didn't change? 
            // Or the ID simply doesn't exist in the table.
            echo json_encode([
                "status" => "error",
                "message" => "Referrer ID not found or No changes made",
                "debug_id" => "[" . $referrer_id . "]" // Brackets to spot hidden spaces
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Incomplete data"
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>
