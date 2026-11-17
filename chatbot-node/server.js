require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('./node_modules/mysql2/promise');

const app = express();
app.use(cors());
app.use(express.json());

// API Key Google Gemini AI
const GEMINI_API_KEY = process.env.GEMINI_API_KEY || "";

// MySQL Connection Pool
const dbPool = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'db_absensi_smk',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Fungsi untuk membaca semua informasi database sistem secara real-time
async function getLiveDatabaseContext(userQuery = '', role = 'pengguna', userId = null, username = 'Tamu') {
    try {
        const [gurus] = await dbPool.query(`
            SELECT g.nama, g.nip, g.mata_pelajaran, COALESCE(u.role, 'guru') as role 
            FROM guru g 
            LEFT JOIN users u ON g.user_id = u.id
        `);

        const [kelases] = await dbPool.query(`
            SELECT k.nama_kelas, k.jurusan, k.tingkat, COALESCE(g.nama, '-') AS wali_kelas, COUNT(s.id) AS total_siswa
            FROM kelas k
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            LEFT JOIN siswa s ON k.id = s.kelas_id
            GROUP BY k.id
            ORDER BY k.id ASC
        `);

        const [totals] = await dbPool.query(`
            SELECT 
                (SELECT COUNT(*) FROM siswa) AS total_siswa,
                (SELECT COUNT(*) FROM guru) AS total_guru,
                (SELECT COUNT(*) FROM kelas) AS total_kelas,
                (SELECT COUNT(*) FROM mata_pelajaran WHERE status = 'active') AS total_mapel,
                (SELECT COUNT(*) FROM jadwal_mengajar WHERE status = 'active') AS total_jadwal
        `);

        const [mapels] = await dbPool.query(`
            SELECT kode_mapel, nama_mapel, kelompok, tingkat, jam_per_minggu 
            FROM mata_pelajaran 
            WHERE status = 'active'
            ORDER BY FIELD(kelompok, 'Muatan Nasional', 'Muatan Kewilayahan', 'Muatan Peminatan Kejuruan', 'Muatan Lokal'), tingkat, nama_mapel ASC
        `);

        // Isolasi Jadwal Sesuai Peran
        let jadwalTitle = "4. DAFTAR JADWAL MENGAJAR:";
        let jadwalList = [];

        if (role === 'guru' || role === 'wali_kelas') {
            let guruId = null;
            let guruNama = username;
            if (userId) {
                const [gp] = await dbPool.query("SELECT id, nama FROM guru WHERE user_id = ? LIMIT 1", [userId]);
                if (gp.length > 0) {
                    guruId = gp[0].id;
                    guruNama = gp[0].nama;
                }
            }

            if (guruId) {
                const [myJadwals] = await dbPool.query(`
                    SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                    FROM jadwal_mengajar jm
                    JOIN kelas k ON jm.kelas_id = k.id
                    LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                    WHERE jm.status = 'active' AND jm.guru_id = ?
                    ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC
                `, [guruId]);

                jadwalTitle = `4. JADWAL MENGAJAR PRIBADI BAPAK/IBU ${guruNama}:\n(PENTING: Ini adalah SATU-SATUNYA jadwal mengajar yang boleh Anda berikan kepada guru ini. Jadwal guru lain TIDAK DISEDIAKAN dan DILARANG dibagikan demi privasi sesama guru!)`;
                if (myJadwals.length > 0) {
                    jadwalList = myJadwals.map(j => `- ${j.hari} (${j.jam_mulai.slice(0, 5)} - ${j.jam_selesai.slice(0, 5)} WIB) | Mapel: ${j.nama_mapel} (${j.kode_mapel}) | Kelas: ${j.nama_kelas} | Ruangan: ${j.ruangan}`);
                } else {
                    jadwalList.push("- Anda tidak memiliki jadwal mengajar aktif di sistem saat ini.");
                }
            }
        } else if (role === 'siswa') {
            let kelasId = null;
            let namaKelas = 'Kelas Anda';
            if (userId) {
                const [sp] = await dbPool.query(`
                    SELECT s.id, s.nama, s.kelas_id, k.nama_kelas 
                    FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id 
                    WHERE s.user_id = ? LIMIT 1
                `, [userId]);
                if (sp.length > 0) {
                    kelasId = sp[0].kelas_id;
                    namaKelas = sp[0].nama_kelas || 'Kelas Anda';
                }
            }

            if (kelasId) {
                const [classJadwals] = await dbPool.query(`
                    SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                    FROM jadwal_mengajar jm
                    JOIN guru g ON jm.guru_id = g.id
                    JOIN kelas k ON jm.kelas_id = k.id
                    LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                    WHERE jm.status = 'active' AND jm.kelas_id = ?
                    ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC
                `, [kelasId]);

                jadwalTitle = `4. JADWAL PELAJARAN KELAS SISWA INI (${namaKelas}):\n(PENTING: Siswa hanya dapat melihat jadwal pelajaran kelasnya sendiri!)`;
                if (classJadwals.length > 0) {
                    jadwalList = classJadwals.map(j => `- ${j.hari} (${j.jam_mulai.slice(0, 5)} - ${j.jam_selesai.slice(0, 5)} WIB) | Mapel: ${j.nama_mapel} (${j.kode_mapel}) | Guru: ${j.nama_guru} | Ruangan: ${j.ruangan}`);
                } else {
                    jadwalList.push(`- Belum ada jadwal pelajaran terdaftar untuk kelas ${namaKelas}.`);
                }
            }
        } else if (role === 'admin' || role === 'kepala_sekolah') {
            const [allJadwals] = await dbPool.query(`
                SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                FROM jadwal_mengajar jm
                JOIN guru g ON jm.guru_id = g.id
                JOIN kelas k ON jm.kelas_id = k.id
                LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                WHERE jm.status = 'active'
                ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC
            `);
            jadwalTitle = "4. DAFTAR JADWAL MENGAJAR SELURUH GURU & KELAS (KBM SEKOLAH):";
            jadwalList = allJadwals.map(j => `- ${j.hari} (${j.jam_mulai.slice(0, 5)} - ${j.jam_selesai.slice(0, 5)} WIB) | Guru: ${j.nama_guru} | Mapel: ${j.nama_mapel} (${j.kode_mapel}) | Kelas: ${j.nama_kelas} | Ruangan: ${j.ruangan}`);
        } else {
            jadwalTitle = "4. INFORMASI JADWAL MENGAJAR:";
            jadwalList.push("- Silakan login untuk melihat jadwal mengajar atau jadwal pelajaran sesuai akun Anda.");
        }

        if (role === 'kepala_sekolah') {
            // EKSKLUSIF KEPALA SEKOLAH: HANYA DATA GURU & JADWAL KBM (TIDAK ADA DATA SISWA)
            const [absenGuruToday] = await dbPool.query(`
                SELECT ag.guru_id, ag.jam_scan, ag.status, ag.keterangan, g.nama, g.nip, g.mata_pelajaran
                FROM absensi_guru ag
                JOIN guru g ON ag.guru_id = g.id
                WHERE ag.tanggal = CURDATE()
            `);

            const guruHadir = absenGuruToday.filter(a => a.status === 'hadir').length;
            const guruTerlambat = absenGuruToday.filter(a => a.status === 'terlambat').length;
            const guruIzin = absenGuruToday.filter(a => a.status === 'izin').length;
            const guruSakit = absenGuruToday.filter(a => a.status === 'sakit').length;
            const guruAlpa = absenGuruToday.filter(a => a.status === 'alpa').length;

            const guruSudahIds = new Set(absenGuruToday.map(a => a.guru_id));
            const guruBelum = gurus.filter(g => !guruSudahIds.has(g.id));

            const [allJadwals] = await dbPool.query(`
                SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                FROM jadwal_mengajar jm
                JOIN guru g ON jm.guru_id = g.id
                JOIN kelas k ON jm.kelas_id = k.id
                LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                WHERE jm.status = 'active'
                ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC
            `);

            const guruList = gurus.map(g => `- ${g.nama} (NIP: ${g.nip || '-'}, Mapel: ${g.mata_pelajaran || '-'})`).join('\n');
            const mapelList = mapels.map(m => `- [${m.kode_mapel}] ${m.nama_mapel} (${m.kelompok}, Tingkat ${m.tingkat}, ${m.jam_per_minggu} JP/minggu)`).join('\n');
            const jadwalList = allJadwals.map(j => `- ${j.hari} (${j.jam_mulai.slice(0, 5)} - ${j.jam_selesai.slice(0, 5)} WIB) | Guru: ${j.nama_guru} | Mapel: ${j.nama_mapel} | Kelas: ${j.nama_kelas} | Ruang: ${j.ruangan}`).join('\n');

            const belumList = guruBelum.map(g => `- ${g.nama} (NIP: ${g.nip || '-'}, Mapel: ${g.mata_pelajaran || '-'})`).join('\n') || '- Seluruh guru telah melakukan presensi hari ini.';
            const sudahList = absenGuruToday.map(a => `- ${a.nama} (NIP: ${a.nip || '-'}) -> Status: ${a.status.toUpperCase()} (Pukul ${a.jam_scan} WIB)`).join('\n') || '- Belum ada guru yang melakukan scan presensi hari ini.';

            return `INFORMASI RESMI DASHBOARD KEPALA SEKOLAH (SMK):
(PENTING: Dashboard Kepala Sekolah HANYA memuat data Dewan Guru, Rekap Presensi Guru, dan Jadwal KBM Guru. Siswa TIDAK diakses dari akun ini!)

1. STATISTIK DEWAN GURU HARI INI:
- Total Dewan Guru & Tendik: ${gurus.length} orang
- Guru Hadir Tepat Waktu: ${guruHadir} orang
- Guru Terlambat: ${guruTerlambat} orang
- Guru Izin: ${guruIzin} orang
- Guru Sakit: ${guruSakit} orang
- Guru Alpa: ${guruAlpa} orang
- Guru Belum Absen / Belum Datang: ${guruBelum.length} orang
- Batas Jam Masuk Guru: 06:45:00 WIB (lewat dari jam ini tercatat terlambat)

2. DAFTAR DEWAN GURU YANG BELUM ABSEN HARI INI (${guruBelum.length} orang):
${belumList}

3. DAFTAR DEWAN GURU YANG SUDAH ABSEN HARI INI (${absenGuruToday.length} orang):
${sudahList}

4. DAFTAR SELURUH DEWAN GURU & MATA PELAJARAN:
${guruList}

5. JADWAL KBM & MENGAJAR DEWAN GURU:
${jadwalList}

6. DAFTAR MATA PELAJARAN:
${mapelList}
`;
        }

        const guruList = gurus.map(g => `- ${g.nama} (NIP: ${g.nip || '-'}, Mapel/Tugas: ${g.mata_pelajaran}, Role: ${g.role})`).join('\n');
        const kelasList = kelases.map(k => `- ${k.nama_kelas}: Jurusan ${k.jurusan}, Tingkat ${k.tingkat}, Wali Kelas: ${k.wali_kelas}, Jumlah Siswa: ${k.total_siswa} orang`).join('\n');
        const mapelList = mapels.map(m => `- [${m.kode_mapel}] ${m.nama_mapel} (${m.kelompok}, Tingkat ${m.tingkat}, ${m.jam_per_minggu} JP/minggu)`).join('\n');

        let context = `INFORMASI RESMI DATABASE SISTEM ABSENSI SMK:
1. STATISTIK SISTEM:
- Total Siswa Terdaftar: ${totals[0].total_siswa} siswa
- Total Guru & Tendik: ${totals[0].total_guru} orang
- Total Kelas: ${totals[0].total_kelas} kelas
- Total Mata Pelajaran: ${totals[0].total_mapel || mapels.length} mapel
- Total Jadwal Mengajar: ${totals[0].total_jadwal || 0} sesi
- Aturan Jam Masuk Siswa: Maksimal pukul 07:00:00 WIB (jika lewat tercatat 'terlambat')
- Aturan Jam Masuk Guru: Maksimal pukul 06:45:00 WIB (jika lewat tercatat 'terlambat')
- Metode Absensi: Barcode/QR Code via kamera web/HP atau input barcode manual, serta RFID untuk siswa.
- Status Kehadiran: hadir, terlambat, izin, sakit, alpa.

2. DAFTAR GURU & STAFF:
${guruList}

3. DAFTAR KELAS & WALI KELAS:
${kelasList}

${jadwalTitle}
${jadwalList.join('\n')}

3. DAFTAR MATA PELAJARAN (KURIKULUM SMK):
${mapelList}
`;

        return context;
    } catch (err) {
        console.error("Gagal membaca database context:", err.message);
        return "Informasi database sistem sedang tidak dapat diakses.";
    }
}

const CANDIDATE_MODELS = [
    'gemini-3.5-flash-lite',
    'gemini-3.1-flash-lite',
    'gemini-3.5-flash'
];

async function callGemini(userMessage, systemPrompt) {
    let lastError = null;

    for (const model of CANDIDATE_MODELS) {
        try {
            const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${GEMINI_API_KEY}`;
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    systemInstruction: {
                        parts: [{ text: systemPrompt }]
                    },
                    contents: [
                        { role: "user", parts: [{ text: userMessage }] }
                    ],
                    generationConfig: {
                        temperature: 0.2, // Rendah agar konsisten dengan data faktual database
                        maxOutputTokens: 350,
                        topP: 0.85
                    }
                })
            });

            if (!response.ok) {
                const errText = await response.text();
                lastError = `Model ${model} error ${response.status}: ${errText}`;
                continue;
            }

            const data = await response.json();
            const text = data?.candidates?.[0]?.content?.parts?.[0]?.text;
            if (text) {
                return text.trim();
            }
        } catch (err) {
            lastError = err.message;
        }
    }

    throw new Error(lastError || "Tidak ada respons dari model Gemini.");
}

app.post('/api/chat', async (req, res) => {
    try {
        const userMessage = req.body.message?.trim();
        const role = req.body.role || 'pengguna';
        const userId = req.body.userId || null;
        const username = req.body.username || 'Tamu';

        if (!userMessage) {
            return res.status(400).json({ success: false, message: "Pesan tidak boleh kosong." });
        }

        // Hard filter untuk permintaan di luar sistem (coding / scripting)
        const forbiddenWords = ['coding', 'script', 'skrip', 'python', 'javascript', 'html', 'css', 'bikin web', 'buat aplikasi', 'bikin kode', 'buatkan kode', 'buat coding', 'tulis kode'];
        const isCoding = forbiddenWords.some(kw => userMessage.toLowerCase().includes(kw));
        if (isCoding && (userMessage.toLowerCase().includes('buat') || userMessage.toLowerCase().includes('bikin') || userMessage.toLowerCase().includes('tulis') || userMessage.toLowerCase().includes('code'))) {
            return res.json({
                success: true,
                message: "Maaf ya kak, saya adalah asisten khusus Sistem Absensi & Informasi SMK ini. Saya hanya dapat menjawab pertanyaan seputar data sistem sekolah, kelas, guru, siswa, dan absensi di sistem ini."
            });
        }

        // Baca informasi database sistem secara langsung sesuai hak akses
        const dbContext = await getLiveDatabaseContext(userMessage, role, userId, username);

        let rolePrompt = "";
        if (role === 'guru') {
            rolePrompt = `ATURAN KHUSUS GURU (${username}):
- Pengguna yang bertanya adalah BAPAK/IBU GURU bernama ${username}.
- Jika ditanya jadwal mengajar (hari ini, besok, hari tertentu seperti Selasa/Rabu/Jumat, dll), HANYA berikan jadwal mengajar pribadi milik ${username} dari data nomor 4 di atas. JANGAN PERNAH memberikan jadwal guru lain.
- Jika pengguna meminta jadwal guru lain, tolak dengan santun demi privasi sesama dewan guru.`;
        } else if (role === 'siswa') {
            rolePrompt = `ATURAN KHUSUS SISWA (${username}):
- Pengguna yang bertanya adalah SISWA bernama ${username}. HANYA berikan jadwal pelajaran untuk kelas siswa ini.`;
        } else if (role === 'kepala_sekolah') {
            rolePrompt = `ATURAN KHUSUS KEPALA SEKOLAH (${username}):
- Pengguna yang bertanya adalah BAPAK/IBU KEPALA SEKOLAH bernama ${username}.
- PENTING DAN MUTLAK: Dashboard Kepala Sekolah HANYA memuat informasi data dewan guru (kehadiran/presensi dewan guru, rekap guru, dan jadwal KBM mengajar guru).
- DILARANG KERAS menyebutkan data absensi siswa, rincian siswa, atau jumlah siswa kepada Kepala Sekolah.
- Jika pengguna menanyakan tentang siswa ("siapa siswa yang belum absen", "rekap absensi siswa", dll), tolak dengan sangat santun:
  "Mohon maaf Bapak/Ibu Kepala Sekolah, sesuai kebijakan hak akses dan cakupan Dashboard Kepala Sekolah, akun Kepala Sekolah difokuskan khusus untuk monitoring dewan guru dan tidak memiliki akses ke data maupun rekapitulasi absensi siswa. Informasi absensi siswa dapat diakses oleh Administrator dan Wali Kelas masing-masing."
- Jawaban untuk pertanyaan umum seputar absensi ("siapa yang belum absen hari ini?", "berapa yang hadir?", dll) HARUS SECARA EKSKLUSIF mengacu pada DEWAN GURU, bukan siswa.`;
        }

        const systemPrompt = `Kamu adalah asisten resmi Sistem Informasi & Absensi SMK yang ramah, sopan, dan hangat layaknya staf sekolah manusia.

Nama pengguna saat ini: ${username} (${role}).

${dbContext}

${rolePrompt}

ATURAN PALING UTAMA & KETAT:
1. BACA & GUNAKAN SELURUH DATA DATABASE DI ATAS untuk menjawab pertanyaan seputar sekolah, jumlah siswa, daftar guru, mata pelajaran, wali kelas, jumlah kelas, aturan jam masuk, status kehadiran, rekap, dan absensi di sistem SMK ini secara faktual dan akurat.
2. DILARANG KERAS MENJAWAB PERTANYAAN DI LUAR SISTEM INI!
   Jika pengguna menanyakan topik di luar sistem sekolah/absensi ini (contoh: coding/skrip, resep masakan, politik, teknologi luar, sejarah umum, selebriti, rumus fisika/matematika umum, cuaca luar, hiburan, game, atau topik umum lainnya):
   KAMU WAJIB MENOLAK SECARA TEGAS DAN SANTUN:
   "Maaf ya kak, saya adalah asisten khusus Sistem Absensi & Informasi SMK ini. Saya hanya dapat menjawab pertanyaan seputar data sistem sekolah, kelas, guru, siswa, dan absensi di sistem ini."
3. GAYA PERCAKAPAN:
   - Jawablah dengan ramah, santun, manusiawi, dan mengalir (1-3 kalimat yang padat dan jelas).
   - Jangan menampilkan analisis, kerangka berpikir, atau format nomor berlebihan.`;

        let answer = await callGemini(userMessage, systemPrompt);

        // Bersihkan tanda bintang markdown berlebihan
        answer = answer.replace(/\*\*/g, '');

        return res.json({
            success: true,
            message: answer
        });

    } catch (error) {
        console.error("Chatbot Server Error:", error.message);
        return res.status(500).json({
            success: false,
            message: "Maaf, saat ini terjadi kendala koneksi ke asisten AI. Silakan coba sesaat lagi ya!"
        });
    }
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`[🤖] Chatbot AI Sistem Absensi SMK berjalan di http://localhost:${PORT}`);
});
