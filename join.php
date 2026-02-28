<?php
// join.php
$invite_id = $_GET['invite_id'] ?? '';
// رابط تطبيقك
$play_store_url = "https://github.com/abappsdevlopers/Bitvews/releases/download/app/BitView.apk";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BitView - انضم إلينا</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 400px;
            width: 90%;
        }

        .logo {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 2px;
            color: #00d2ff;
        }

        .loader {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #00d2ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        h1 { font-size: 1.5rem; margin-bottom: 10px; }
        p { font-size: 1rem; color: #e0e0e0; margin-bottom: 20px; }

        .footer-note {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-top: 30px;
        }

        /* تنسيق مخفي لعملية النسخ */
        #temp_clipboard {
            position: absolute;
            left: -9999px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">BitView</div>
        <h1>مرحباً بك في عالم الربح!</h1>
        <p>جاري تحضير رابط التحميل الآمن وتجهيز كود الإحالة الخاص بك...</p>
        
        <div class="loader"></div>
        
        <p id="status_msg">يرجى الانتظار ثانية واحدة...</p>

        <div class="footer-note">
            ستبدأ عملية التحميل تلقائياً، تأكد من تثبيت التطبيق والبدء في الكسب.
        </div>
    </div>

    <textarea id="temp_clipboard"><?php echo htmlspecialchars($invite_id); ?></textarea>

    <script>
        window.onload = function() {
            var inviteId = "<?php echo $invite_id; ?>";
            var statusMsg = document.getElementById('status_msg');

            // وظيفة النسخ الذكية
            function copyAction() {
                var copyText = document.getElementById("temp_clipboard");
                if (inviteId !== "") {
                    copyText.select();
                    copyText.setSelectionRange(0, 99999); // للهواتف
                    document.execCommand("copy");
                    console.log("Copied ID: " + inviteId);
                }
            }

            // تنفيذ النسخ
            copyAction();

            // توجيه المستخدم بعد 2 ثانية ليعطي انطباع بالاحترافية
            setTimeout(function() {
                statusMsg.innerText = "بدء التحميل الآن...";
                window.location.href = "<?php echo $play_store_url; ?>";
            }, 2500);
        };
    </script>
</body>
</html>
