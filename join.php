<?php
// join.php
$invite_id = $_GET['invite_id'] ?? '';

// هنا نضع رابط تطبيقك على المتجر
$play_store_url = "https://github.com/abappsdevlopers/Bitvews/releases/download/app/BitView.apk";

// إعادة توجيه المستخدم للمتجر ليحمل التطبيق
header("Location: " . $play_store_url);
exit;
?>
