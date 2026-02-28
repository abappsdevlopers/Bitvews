<?php
// join.php
$invite_id = $_GET['invite_id'] ?? '';
$play_store_url = "https://github.com/abappsdevlopers/Bitvews/releases/download/app/BitView.apk";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BitView - انضم واربح</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #38bdf8;
            --bg: #0f172a;
            --card-bg: #1e293b;
        }

        /* ضبط الهيكل الأساسي لمنع التمدد الزائد */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* يمنع التمرير في الصفحة الخارجية */
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .container {
            width: 100%;
            max-width: 420px; /* عرض مثالي للحاسوب والهاتف */
            padding: 20px;
            box-sizing: border-box;
        }

        .mobile-card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 15px; /* توزيع المسافات بين العناصر بشكل آلي */
        }

        .gift-icon {
            font-size: 50px;
            background: rgba(56, 189, 248, 0.1);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 20px;
            margin: 0 auto;
        }

        h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 900;
            color: var(--primary);
        }

        p {
            font-size: 15px;
            color: #94a3b8;
            margin: 0;
            line-height: 1.5;
        }

        .invite-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed var(--primary);
            padding: 8px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-download {
            background: var(--primary);
            color: var(--bg);
            border: none;
            padding: 15px;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
        }

        .btn-download:active {
            transform: scale(0.95);
        }

        .footer-text {
            font-size: 11px;
            color: #64748b;
        }

        /* حقل مخفي تماماً */
        #inviteCode { position: absolute; opacity: 0; pointer-events: none; }
    </style>
</head>
<body>

    <div class="container">
        <div class="mobile-card">
            <div class="gift-icon">🎁</div>
            
            <h1>هدية انضمام!</h1>
            
            <p>صديقك يدعوك لتجربة <b>BitView</b>. حمل التطبيق الآن وسيتم تفعيل كود الإحالة تلقائياً للحصول على مكافأتك.</p>

            <div class="invite-badge">
                كود الدعوة: #<?php echo htmlspecialchars($invite_id); ?>
            </div>

            <button onclick="handleAction()" class="btn-download" id="mainBtn">
                تفعيل المكافأة والتحميل
            </button>

            <div class="footer-text">
                سيتم نسخ الكود وبدء التنزيل فور الضغط
            </div>
        </div>
    </div>

    <input type="text" value="<?php echo htmlspecialchars($invite_id); ?>" id="inviteCode">

    <script>
    function handleAction() {
        // النسخ
        const copyText = document.getElementById("inviteCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

        // تغيير الحالة بصرياً
        const btn = document.getElementById('mainBtn');
        btn.innerHTML = "تم التفعيل.. جاري التحميل";
        btn.style.background = "#22c55e";

        // التنزيل
        setTimeout(() => {
            window.location.href = "<?php echo $play_store_url; ?>";
        }, 600);
    }
    </script>
</body>
</html>
