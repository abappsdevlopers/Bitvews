<?php
// referral.php
header("Content-Type: application/json");

// الاتصال بقاعدة بياناتك (Firebase أو MySQL)
$new_user_id = $_POST['new_user_id'];
$referrer_id = $_POST['referrer_id']; // الـ ID الخاص بالشخص الذي أرسل الرابط

if (!empty($referrer_id)) {
    // 1. أضف عملات للشخص الذي قام بالدعوة (مثلاً 500 عملة)
    // نرسل طلب لـ Firebase لزيادة الرصيد لـ $referrer_id
    
    // 2. سجل أن المستخدم الجديد تم دعوته لكي لا تتكرر المكافأة
    echo json_encode(["status" => "success", "reward" => 500]);
} else {
    echo json_encode(["status" => "no_referrer"]);
}
?>
