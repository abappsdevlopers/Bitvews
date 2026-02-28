<?php
// join.php
$invite_id = $_GET['invite_id'] ?? '';
$play_store_url = "https://github.com/abappsdevlopers/Bitvews/releases/download/app/BitView.apk";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BitView - هدية بانتظارك</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #0f172a;
            color: white;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .card {
            background: #1e293b;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            width: 90%;
            max-width: 380px;
            border: 1px solid #334155;
        }
        .icon-box {
            font-size: 50px;
            margin-bottom: 10px;
        }
        h1 { font-size: 22px; color: #38bdf8; margin-bottom: 5px; }
        p { color: #94a3b8; font-size: 15px; line-height: 1.6; }
        
        .download-btn {
            display: block;
            background: #38bdf8;
            color: #0f172a;
            text-decoration: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18px;
            margin-top: 25px;
            transition: transform 0.2s, background 0.2s;
            border: none;
            width: 100%;
            cursor: pointer;
        }
        .download-btn:active { transform: scale(0.95); background: #7dd3fc; }
        
        .badge {
            display: inline-block;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-box">🎁</div>
        <h1>لقد تمت دعوتك!</h1>
        <p>قم بتحميل التطبيق الآن للحصول على مكافأتك. سيتم تفعيل كود الإحالة تلقائياً عند الضغط على الزر.</p>
        
        <div class="badge">كود الدعوة جاهز للتفعيل ✅</div>

        <button onclick="copyAndDownload()" class="download-btn">
            تحميل وتفعيل المكافأة
        </button>

        <p style="font-size: 11px; margin-top: 15px;">بضغطك على الزر، أنت توافق على شروط الخدمة.</p>
    </div>

    <input type="text" value="<?php echo htmlspecialchars($invite_id); ?>" id="inviteCode" style="position: absolute; left: -9999px;">

    <script>
    function copyAndDownload() {
        // 1. عملية النسخ (تعمل هنا لأنها مرتبطة بضغط الزر)
        var copyText = document.getElementById("inviteCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // للهواتف
        
        try {
            document.execCommand("copy");
            console.log("Copied: " + copyText.value);
        } catch (err) {
            console.log("Unable to copy");
        }

        // 2. التوجيه للتحميل بعد إتمام النسخ بـ 300 مللي ثانية
        setTimeout(function() {
            window.location.href = "<?php echo $play_store_url; ?>";
        }, 300);
    }
    </script>

</body>
</html>
