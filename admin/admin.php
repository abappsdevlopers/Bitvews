<?php
include 'db.php';

// تحديث حالة السحب إذا تم إرسال طلب
if (isset($_POST['update_withdraw'])) {
    $id = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE withdraws SET status='$status' WHERE id=$id");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <title>لوحة التحكم</title>
</head>
<body class="p-4">
    <h2>لوحة إدارة التطبيق</h2>
    
    <h3>طلبات السحب</h3>
    <table class="table table-bordered">
        <thead>
            <tr><th>المستخدم</th><th>البريد (PayPal)</th><th>المبلغ</th><th>الحالة</th><th>إجراء</th></tr>
        </thead>
        <tbody>
            <?php
            $withdraws = $conn->query("SELECT * FROM withdraws ORDER BY created_at DESC");
            while($row = $withdraws->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['user_id']}</td>
                    <td>{$row['paypal_email']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <select name='status'>
                                <option value='PENDING'>قيد الانتظار</option>
                                <option value='COMPLETED'>مكتمل</option>
                            </select>
                            <button type='submit' name='update_withdraw' class='btn btn-sm btn-primary'>تحديث</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

    <h3>المستخدمون</h3>
    <table class="table table-striped">
        <thead>
            <tr><th>الاسم</th><th>البريد</th><th>العملات</th><th>الانطباعات</th></tr>
        </thead>
        <tbody>
            <?php
            $users = $conn->query("SELECT * FROM users");
            while($row = $users->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['user_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['coins']}</td>
                    <td>{$row['impressions']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
