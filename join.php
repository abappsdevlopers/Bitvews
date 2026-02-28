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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #38bdf8;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .mobile-card {
            width: 90%;
            max-width: 400px;
            background: var(--card-bg);
            border-radius: 32px;
            padding: 40px 24px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .gift-icon {
            width: 80px;
            height: 80px;
            background: rgba(56, 189, 248, 0.1);
            border-radius: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            margin: 0 auto 24px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 12px;
            background: linear-gradient(to right, #fff, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 16px;
            color: var(--text-dim);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .invite-badge {
            background: rgba(56, 189, 248, 0.08);
            border: 1px dashed var(--primary);
            padding: 10px;
            border-radius: 16px;
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 32px;
            display: block;
        }

        .btn-download {
            background: var(--primary);
            color: var(--bg);
            width: 100%;
            padding: 18px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(56, 189, 248, 0.3);
            transition: all 0.2s;
        }

        .btn-download:active {
            transform: scale(0.96);
            filter: brightness(0.9);
        }

        .step {
            font-size: 12px;
            color: var(--text-dim);
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="mobile-card">
        <div class="gift-icon">🎁</div>
        
        <h1>هدية بانتظارك!</h1>
        <p>لقد أرسل لك صديقك دعوة للانضمام إلى <b>BitView</b>. اضغط أدناه لتحميل التطبيق وتفعيل مكافأتك فوراً.</p>

        <span class="invite-badge">
            كود الدعوة: #<?php echo htmlspecialchars($invite_id); ?> جاهز ✅
        </span>

        <button onclick="handleAction()" class="btn-download">
            تحميل وتفعيل الآن
        </button>

        <div class="step">
            سيتم نسخ كود المكافأة وبدء التحميل تلقائياً
        </div>
    </div>

    <input type="text" value="<?php echo htmlspecialchars($invite_id); ?>" id="inviteCode" style="position: absolute; left: -9999px;">

    <script>
    function handleAction() {
        // 1. نسخ الكود (يعمل مع ضغطة الزر)
        const copyText = document.getElementById("inviteCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

        // تغيير نص الزر ليعطي انطباع بالنجاح
        const btn = document.querySelector('.btn-download');
        btn.innerHTML = "تم النسخ.. جاري التحميل";
        btn.style.background = "#22c55e"; // تغيير للون الأخضر

        // 2. بدء التنزيل بعد تأخير بسيط جداً
        setTimeout(() => {
            window.location.href = "<?php echo $play_store_url; ?>";
        }, 500);
    }
    </script>
</body>
</html>
