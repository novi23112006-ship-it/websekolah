<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absen Siswa - Cetak</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        h2, h4 { text-align: center; margin: 0; }
        .header-info { text-align: center; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 5px 8px; text-align: left; }
        th { background-color: #e0e0e0; }
        .badge-hadir { color: green; font-weight: bold; }
        .badge-terlambat { color: orange; font-weight: bold; }
        .badge-alpa { color: red; font-weight: bold; }
        .badge-izin { color: blue; font-weight: bold; }
        .badge-sakit { color: teal; font-weight: bold; }
        .no-print { display: none; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header-info">
        <h2>Sistem Absensi SMK</h2>
        <h4>Riwayat Absen Siswa</h4>
        <?php
        $info_filter = [];
        if(!empty($data['filters']['tanggal_mulai'])) $info_filter[] = 'Mulai: ' . date('d M Y', strtotime($data['filters']['tanggal_mulai']));
        if(!empty($data['filters']['tanggal_akhir'])) $info_filter[] = 'Sampai: ' . date('d M Y', strtotime($data['filters']['tanggal_akhir']));
        if(!empty($info_filter)): ?>
        <p style="margin-top: 4px;">Periode: <?= implode(' — ', $info_filter) ?></p>
        <?php else: ?>
        <p style="margin-top: 4px;">Semua Periode</p>
        <?php endif; ?>
        <p>Dicetak pada: <?= date('d M Y, H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach($data['laporan'] as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= htmlspecialchars($row['nis']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                <td><?= $row['jam_masuk'] ?? '-' ?></td>
                <td><?= !empty($row['jam_pulang']) ? $row['jam_pulang'] : '-' ?></td>
                <td class="badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
                <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($data['laporan'])): ?>
            <tr><td colspan="8" style="text-align:center;">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="margin-top: 12px;">Total: <?= count($data['laporan']) ?> data.</p>

    <div class="no-print" style="display: block; margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 24px; font-size: 14px; cursor: pointer;">🖨️ Cetak / Simpan PDF</button>
        <button onclick="window.close()" style="padding: 8px 24px; font-size: 14px; cursor: pointer; margin-left: 10px;">✕ Tutup</button>
    </div>

    <script>
        // Auto print on page load (comment out jika tidak ingin otomatis)
        // window.onload = () => window.print();
    </script>
</body>
</html>
