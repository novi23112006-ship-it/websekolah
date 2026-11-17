<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']); ?> - <?= htmlspecialchars($data['guru']['nama']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            background-color: #f8fafc;
            margin: 0;
            padding: 40px 20px;
        }
        .no-print {
            margin-bottom: 25px;
        }
        .btn-action {
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            margin: 0 5px;
        }
        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }
        .btn-close {
            background-color: #64748b;
            color: #ffffff;
        }
        .barcode-card {
            border: 2px solid #1e293b;
            background: #ffffff;
            display: inline-block;
            padding: 24px;
            border-radius: 16px;
            width: 320px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            text-align: center;
            position: relative;
        }
        .card-header-badge {
            background: #1e3a8a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 12px;
        }
        .sekolah-name {
            font-weight: 800;
            font-size: 16px;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .sekolah-sub {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 16px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 10px;
        }
        #qrcode {
            display: flex;
            justify-content: center;
            margin: 15px auto;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 160px;
            height: 160px;
        }
        .barcode-text {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        .guru-name {
            font-size: 16px;
            margin-top: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .guru-nip {
            font-size: 13px;
            color: #475569;
            margin-top: 3px;
        }
        .guru-mapel {
            font-size: 12px;
            color: #1d4ed8;
            font-weight: 600;
            margin-top: 4px;
        }
        .card-footer-info {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            font-size: 10px;
            color: #94a3b8;
        }
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .barcode-card {
                box-shadow: none;
                border: 2px solid #000;
                margin-top: 20px;
                page-break-inside: avoid;
            }
        }
    </style>
    <!-- Include QRCode JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

    <div class="no-print">
        <button class="btn-action btn-print" onclick="window.print()">🖨️ Cetak Kartu Barcode</button>
        <button class="btn-action btn-close" onclick="window.close()">✕ Tutup</button>
    </div>

    <div class="barcode-card">
        <div class="card-header-badge">KARTU ABSENSI RESMI</div>
        <div class="sekolah-name">SMK DIGITAL ACADEMY</div>
        <div class="sekolah-sub">Sistem Absensi & Presensi Terpadu</div>
        
        <!-- Placeholder for QR Code -->
        <div id="qrcode"></div>
        <div class="barcode-text"><?= htmlspecialchars($data['guru']['barcode']); ?></div>
        
        <div class="guru-name"><?= htmlspecialchars($data['guru']['nama']); ?></div>
        <div class="guru-nip">NIP: <?= htmlspecialchars($data['guru']['nip'] ?: '-'); ?></div>
        <div class="guru-mapel"><?= htmlspecialchars($data['guru']['mata_pelajaran'] ?: 'Dewan Guru'); ?></div>

        <div class="card-footer-info">
            Pindai QR Code ini pada Terminal Kamera Absensi Sekolah
        </div>
    </div>

    <script>
        // Render QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= addslashes($data['guru']['barcode']); ?>",
            width: 160,
            height: 160,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        
        // Auto print
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 600);
        };
    </script>
</body>
</html>
