<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gangguan sementara</title>
    <script>
        (function () {
            var key = 'absensi_500_reloaded';
            try {
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    window.location.reload();
                    return;
                }
                sessionStorage.removeItem(key);
            } catch (e) {}
        })();
    </script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f3f4f6; color: #1f2937; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
        .box { background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); max-width: 420px; text-align: center; }
    </style>
</head>
<body>
    <div class="box">
        <h1 style="font-size: 1.25rem; margin: 0 0 8px;">Gangguan sementara</h1>
        <p style="margin: 0; color: #4b5563;">Halaman sedang dimuat ulang. Silakan lanjutkan pekerjaan Anda.</p>
    </div>
</body>
</html>
