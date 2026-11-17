<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul']; ?> - <?= htmlspecialchars($data['siswa']['nama']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .barcode-card {
            border: 2px solid #000;
            display: inline-block;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }
        .sekolah-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .siswa-name {
            font-size: 16px;
            margin-top: 15px;
            font-weight: bold;
        }
        .nis-text {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .barcode-text {
            font-family: monospace;
            margin-top: 10px;
            font-size: 14px;
        }
        #qrcode {
            display: flex;
            justify-content: center;
            margin: 0 auto;
        }
        @media print {
            .no-print {
                display: none;
            }
            .barcode-card {
                border: 1px solid #000; /* Adjust for printing */
            }
        }
    </style>
    <!-- Include QRCode JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-size: 16px;">Cetak Barcode</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; font-size: 16px;">Tutup</button>
    </div>

    <div class="barcode-card">
        <div class="sekolah-name">Kartu Absensi SMK</div>
        
        <!-- Placeholder for QR Code -->
        <div id="qrcode"></div>
        <div class="barcode-text"><?= htmlspecialchars($data['siswa']['barcode']); ?></div>
        
        <div class="siswa-name"><?= htmlspecialchars($data['siswa']['nama']); ?></div>
        <div class="nis-text">NIS: <?= htmlspecialchars($data['siswa']['nis']); ?></div>
    </div>

    <script>
        // Render QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= addslashes($data['siswa']['barcode']); ?>",
            width: 150,
            height: 150,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        
        // Auto print after render
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
