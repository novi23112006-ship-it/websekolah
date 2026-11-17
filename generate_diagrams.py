import os
import matplotlib.pyplot as plt
import matplotlib.patches as patches

# Global settings
plt.rcParams['font.sans-serif'] = 'Arial'
plt.rcParams['axes.edgecolor'] = '#CCCCCC'
out_dir = r"C:\xampp1\htdocs\websekolah3\laporan word"
os.makedirs(out_dir, exist_ok=True)

# ══════════════════════════════════════════════════════════════════════════════
# 1. USE CASE DIAGRAM
# ══════════════════════════════════════════════════════════════════════════════
def draw_use_case():
    fig, ax = plt.subplots(figsize=(13, 10.5), dpi=300)
    ax.set_xlim(0, 130)
    ax.set_ylim(0, 105)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # System boundary box
    sys_box = patches.FancyBboxPatch((30, 4), 70, 95, boxstyle="round,pad=1,rounding_size=1.5",
                                     edgecolor='#1B365D', facecolor='#F8FAFC', linewidth=2.2)
    ax.add_patch(sys_box)
    ax.text(65, 96, "Sistem Informasi Presensi & Monitoring Absensi SMK",
            ha='center', va='center', fontsize=12, fontweight='bold', color='#1B365D')

    def draw_actor(x, y, name, role_desc):
        circle = patches.Circle((x, y + 4.0), 1.8, edgecolor='#1B365D', facecolor='#E2E8F0', linewidth=2)
        ax.add_patch(circle)
        ax.plot([x, x], [y + 2.2, y - 2.8], color='#1B365D', lw=2)
        ax.plot([x - 2.8, x + 2.8], [y + 0.6, y + 0.6], color='#1B365D', lw=2)
        ax.plot([x, x - 2.2], [y - 2.8, y - 7.0], color='#1B365D', lw=2)
        ax.plot([x, x + 2.2], [y - 2.8, y - 7.0], color='#1B365D', lw=2)
        ax.text(x, y - 9.2, name, ha='center', va='top', fontsize=9.5, fontweight='bold', color='#1B365D')
        ax.text(x, y - 12.2, role_desc, ha='center', va='top', fontsize=8, fontstyle='italic', color='#555555')

    draw_actor(14, 82, "Super Admin", "(Pengelola Master Data\n& Multi-Provider AI)")
    draw_actor(14, 50, "Dewan Guru", "(Presensi Barcode\n& Pengajuan Izin)")
    draw_actor(14, 18, "Siswa", "(Presensi Mandiri\n& Asisten Virtual AI)")

    draw_actor(116, 68, "Wali Kelas", "(Monitoring Kelas\n& Catatan Kedisiplinan)")
    draw_actor(116, 32, "Kepala Sekolah", "(Monitoring Eksekutif\n& Laporan Kehadiran)")

    def draw_uc(cx, cy, w, h, title):
        ellipse = patches.Ellipse((cx, cy), w, h, edgecolor='#2563EB', facecolor='#EFF6FF', linewidth=1.6)
        ax.add_patch(ellipse)
        ax.text(cx, cy, title, ha='center', va='center', fontsize=8.2, color='#0F172A', fontweight='bold')
        return (cx, cy)

    draw_uc(65, 88, 30, 6.8, "Login & Autentikasi Sistem (Bcrypt)")
    draw_uc(48, 77, 28, 6.2, "Kelola Master Data (Guru, Siswa, Kelas)")
    draw_uc(48, 67, 28, 6.2, "Kelola Jadwal & Hari Operasional")
    draw_uc(48, 57, 28, 6.2, "Pengaturan Radius Geofencing (100m)")
    draw_uc(48, 47, 28, 6.2, "Manajemen API Key Multi-Provider AI")

    draw_uc(82, 77, 28, 6.2, "Presensi Barcode/QR Scanner Kamera")
    draw_uc(82, 67, 28, 6.2, "Presensi Mandiri Kartu RFID")
    draw_uc(82, 57, 28, 6.2, "Pengajuan Izin & Sakit Online")
    draw_uc(65, 36, 30, 6.8, "Konsultasi Asisten Virtual AI (Chatbot)")

    draw_uc(82, 25, 28, 6.2, "Monitoring Presensi Kelas Binaan")
    draw_uc(82, 14, 28, 6.2, "Manajemen Catatan Kedisiplinan")
    draw_uc(48, 25, 28, 6.2, "Monitoring Eksekutif Dewan Guru")
    draw_uc(48, 14, 28, 6.2, "Unduh Rekap Laporan Presensi")

    def connect(x1, y1, x2, y2):
        ax.plot([x1, x2], [y1, y2], color='#64748B', linestyle='-', lw=1.2)

    # Admin
    connect(18, 80, 50, 88)
    connect(18, 80, 34, 77)
    connect(18, 80, 34, 67)
    connect(18, 80, 34, 57)
    connect(18, 80, 34, 47)

    # Guru
    connect(18, 50, 50, 88)
    connect(18, 50, 68, 77)
    connect(18, 50, 68, 57)
    connect(18, 50, 50, 36)

    # Siswa
    connect(18, 18, 50, 88)
    connect(18, 18, 68, 77)
    connect(18, 18, 68, 67)
    connect(18, 18, 68, 57)
    connect(18, 18, 50, 36)

    # Wali Kelas
    connect(112, 68, 80, 88)
    connect(112, 68, 96, 25)
    connect(112, 68, 96, 14)

    # Kepala Sekolah
    connect(112, 32, 80, 88)
    connect(112, 32, 62, 25)
    connect(112, 32, 62, 14)

    ax.annotate("<<include>>\nValidasi Radius", xy=(48, 60.5), xytext=(68, 71.5),
                arrowprops=dict(arrowstyle="->", color="#DC2626", lw=1.3, ls="--"),
                fontsize=7.8, color="#DC2626", ha="center", fontweight='bold')
    
    ax.annotate("<<extend>>\nNotifikasi Bot", xy=(65, 84.5), xytext=(83, 83.5),
                arrowprops=dict(arrowstyle="->", color="#16A34A", lw=1.3, ls="--"),
                fontsize=7.8, color="#16A34A", ha="center", fontweight='bold')

    plt.tight_layout()
    out_path = os.path.join(out_dir, "use_case_diagram.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Use Case Diagram saved.")

# ══════════════════════════════════════════════════════════════════════════════
# 2. ACTIVITY DIAGRAM 1: PRESENSI GEOFENCING & EVALUASI JADWAL
# ══════════════════════════════════════════════════════════════════════════════
def draw_activity_presensi():
    fig, ax = plt.subplots(figsize=(11, 13), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 125)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    ax.axvline(33.3, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')
    ax.axvline(66.6, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')

    def draw_lane_hdr(x1, x2, title):
        rect = patches.Rectangle((x1, 118), x2 - x1, 5.5, facecolor='#1B365D', edgecolor='#0F172A', lw=1)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 120.7, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=9.5)

    draw_lane_hdr(0, 33.3, "PENGGUNA (SISWA / GURU)")
    draw_lane_hdr(33.3, 66.6, "FRONTEND WEB SCANNER")
    draw_lane_hdr(66.6, 100, "BACKEND PHP & DATABASE")

    def draw_action(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.6,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    def draw_decision(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.8], [cx + 3.8, cy], [cx, cy - 2.8], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 4.2, label, ha='center', va='top', fontsize=7.5, color='#B45309', fontweight='bold')
        return (cx, cy)

    def arrow(x1, y1, x2, y2, label="", lbl_pos='right'):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.3, mutation_scale=12))
        if label:
            mx, my = (x1 + x2)/2, (y1 + y2)/2
            dx = 1.8 if lbl_pos == 'right' else -1.8
            ax.text(mx + dx, my, label, fontsize=7.2, color="#0F172A", fontweight='bold', ha='left' if lbl_pos=='right' else 'right')

    # Start
    c_start = patches.Circle((16.6, 114), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    draw_action(16.6, 106, 26, 5.5, "Membuka Menu Scanner\n(Barcode / QR Kamera)")
    arrow(16.6, 112.2, 16.6, 108.8)

    draw_action(50.0, 106, 26, 5.5, "Minta Akses Lokasi GPS\n& Inisialisasi Kamera")
    arrow(29.6, 106, 37.0, 106)

    draw_action(16.6, 96, 26, 5.5, "Memberikan Izin Akses\nGeolokasi Browser")
    arrow(50.0, 103.2, 50.0, 98.8)
    arrow(50.0, 98.8, 29.6, 96)

    draw_action(16.6, 86, 26, 5.5, "Pindai Barcode ID /\nTap Kartu RFID Terminal")
    arrow(16.6, 93.2, 16.6, 88.8)

    draw_action(50.0, 86, 26, 5.5, "Kirim Payload POST AJAX\n(Barcode, Lat, Lng, CSRF)")
    arrow(29.6, 86, 37.0, 86)

    draw_action(83.3, 86, 27, 5.5, "Kalkulasi Jarak GPS Sekolah\n(Formula Haversine)")
    arrow(63.0, 86, 69.8, 86)

    draw_decision(83.3, 76, "Jarak <= 100 Meter?")
    arrow(83.3, 83.2, 83.3, 78.8)

    draw_action(50.0, 76, 26, 5.5, "Tampilkan Error:\nDi Luar Radius 100m", bg='#FEE2E2', border='#DC2626')
    arrow(79.5, 76, 63.0, 76, "[Tidak]")

    draw_action(83.3, 65, 27, 5.5, "Evaluasi Jadwal Hari Ini\n(Jadwal_model Aktif)")
    arrow(83.3, 73.2, 83.3, 67.8, "[Ya]")

    draw_decision(83.3, 54, "Jadwal Ditemukan?")
    arrow(83.3, 62.2, 83.3, 56.8)

    draw_action(50.0, 54, 26, 5.5, "Tolak: Jadwal Hari Ini\nBelum Dikonfigurasi", bg='#FEE2E2', border='#DC2626')
    arrow(79.5, 54, 63.0, 54, "[Tidak]")

    draw_decision(83.3, 42, "Waktu <= Batas Masuk?")
    arrow(83.3, 51.2, 83.3, 44.8, "[Ya]")

    draw_action(72.0, 31, 20, 5.0, "Status Kehadiran:\nHadir (Tepat Waktu)", bg='#DCFCE7', border='#16A34A')
    draw_action(94.0, 31, 20, 5.0, "Status Kehadiran:\nTerlambat (Lewat Jam)", bg='#FEF3C7', border='#D97706')

    arrow(79.5, 42, 72.0, 33.5, "[Ya]")
    arrow(87.1, 42, 94.0, 33.5, "[Tidak]")

    draw_action(83.3, 20, 27, 5.5, "Simpan Transaksi Presensi\nke Database (CURDATE)")
    arrow(72.0, 28.5, 80.0, 22.8)
    arrow(94.0, 28.5, 86.6, 22.8)

    draw_action(83.3, 11, 27, 5.0, "Trigger Dispatch Telegram\n(Dual-Fallback cURL/Stream)")
    arrow(83.3, 17.2, 83.3, 13.5)

    draw_action(50.0, 11, 26, 5.0, "Tampilkan Kartu Presensi\n& Notifikasi SweetAlert2", bg='#DCFCE7', border='#16A34A')
    arrow(69.8, 11, 63.0, 11)

    draw_action(16.6, 11, 26, 5.0, "Melihat Hasil Presensi\n& Status Kehadiran Diterima")
    arrow(37.0, 11, 29.6, 11)

    def draw_end(cx, cy):
        c1 = patches.Circle((cx, cy), 2.2, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
        c2 = patches.Circle((cx, cy), 1.3, facecolor='#0F172A', edgecolor='#0F172A')
        ax.add_patch(c1)
        ax.add_patch(c2)

    draw_end(16.6, 3.5)
    arrow(16.6, 8.5, 16.6, 5.7)

    arrow(37.0, 76, 24.0, 76)
    ax.annotate("", xy=(16.6, 5.7), xytext=(24.0, 76),
                arrowprops=dict(arrowstyle="-|>", color="#DC2626", lw=1.2, ls=':'))

    plt.tight_layout()
    out_path = os.path.join(out_dir, "activity_diagram_presensi.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram Presensi saved.")

# ══════════════════════════════════════════════════════════════════════════════
# 3. ACTIVITY DIAGRAM 2: ASISTEN VIRTUAL MULTI-PROVIDER AI
# ══════════════════════════════════════════════════════════════════════════════
def draw_activity_ai():
    fig, ax = plt.subplots(figsize=(11, 11), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 105)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    ax.axvline(33.3, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')
    ax.axvline(66.6, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')

    def draw_lane_hdr(x1, x2, title):
        rect = patches.Rectangle((x1, 98), x2 - x1, 5.5, facecolor='#1B365D', edgecolor='#0F172A', lw=1)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 100.7, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=9.5)

    draw_lane_hdr(0, 33.3, "PENGGUNA (DASHBOARD)")
    draw_lane_hdr(33.3, 66.6, "BACKEND & CONTEXT INJECTOR")
    draw_lane_hdr(66.6, 100, "AI ENGINE (GEMINI / OPENAI)")

    def draw_action(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.6,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    def draw_decision(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.8], [cx + 3.8, cy], [cx, cy - 2.8], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 4.2, label, ha='center', va='top', fontsize=7.5, color='#B45309', fontweight='bold')
        return (cx, cy)

    def arrow(x1, y1, x2, y2, label=""):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.3, mutation_scale=12))
        if label:
            mx, my = (x1 + x2)/2, (y1 + y2)/2
            ax.text(mx + 1.8, my, label, fontsize=7.2, color="#0F172A", fontweight='bold')

    c_start = patches.Circle((16.6, 94), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    draw_action(16.6, 85, 26, 5.5, "Klik Widget Chatbot &\nKetik Pertanyaan Bahasa Alami")
    arrow(16.6, 92.2, 16.6, 87.8)

    draw_action(50.0, 85, 26, 5.5, "Validasi CSRF Token,\nSesi Role & Sanitasi Prompt")
    arrow(29.6, 85, 37.0, 85)

    draw_action(50.0, 73, 26, 5.5, "Inject Konteks Dinamis Database\n(Profil, Jadwal, Rekap Hadir)")
    arrow(50.0, 82.2, 50.0, 75.8)

    draw_action(50.0, 61, 26, 5.5, "Ambil Konfigurasi AI Aktif\n(Ai_model::getActiveConfig)")
    arrow(50.0, 70.2, 50.0, 63.8)

    draw_action(50.0, 49, 26, 5.5, "Instansiasi Adapter Provider\n(GeminiAdapter / OpenAiAdapter)")
    arrow(50.0, 58.2, 50.0, 51.8)

    draw_action(83.3, 49, 27, 5.5, "Kirim Payload HTTPS POST JSON\n(Prompt + System Instruction)")
    arrow(63.0, 49, 69.8, 49)

    draw_decision(83.3, 37, "API Response OK?")
    arrow(83.3, 46.2, 83.3, 39.8)

    draw_action(83.3, 25, 26, 5.0, "Ekstraksi Teks Jawaban AI\n(Format Markdown Bersih)", bg='#DCFCE7', border='#16A34A')
    arrow(83.3, 34.2, 83.3, 27.5, "[Ya]")

    draw_action(50.0, 37, 26, 5.0, "Kembalikan Pesan Ramah\n(Fallback Aman Tanpa Bocor Key)", bg='#FEE2E2', border='#DC2626')
    arrow(79.5, 37, 63.0, 37, "[Gagal/Limit]")

    draw_action(50.0, 15, 26, 5.5, "Kemas JSON Response\n{success: true, message: '...'}")
    arrow(83.3, 22.5, 63.0, 15)
    arrow(50.0, 34.5, 50.0, 17.8)

    draw_action(16.6, 15, 26, 5.5, "Tampilkan Balasan Chatbot\ndi Gelembung Percakapan", bg='#DCFCE7', border='#16A34A')
    arrow(37.0, 15, 29.6, 15)

    def draw_end(cx, cy):
        c1 = patches.Circle((cx, cy), 2.2, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
        c2 = patches.Circle((cx, cy), 1.3, facecolor='#0F172A', edgecolor='#0F172A')
        ax.add_patch(c1)
        ax.add_patch(c2)

    draw_end(16.6, 4.5)
    arrow(16.6, 12.2, 16.6, 6.7)

    plt.tight_layout()
    out_path = os.path.join(out_dir, "activity_diagram_ai.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram AI saved.")

# ══════════════════════════════════════════════════════════════════════════════
# 4. ACTIVITY DIAGRAM 3: NOTIFIKASI REAL-TIME TELEGRAM BOT
# ══════════════════════════════════════════════════════════════════════════════
def draw_activity_telegram():
    fig, ax = plt.subplots(figsize=(11, 10), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 95)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    ax.axvline(33.3, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')
    ax.axvline(66.6, ymin=0.03, ymax=0.96, color='#94A3B8', lw=1.5, ls='--')

    def draw_lane_hdr(x1, x2, title):
        rect = patches.Rectangle((x1, 88), x2 - x1, 5.5, facecolor='#1B365D', edgecolor='#0F172A', lw=1)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 90.7, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=9.5)

    draw_lane_hdr(0, 33.3, "TRANSAKSI PRESENSI (EVENT)")
    draw_lane_hdr(33.3, 66.6, "TELEGRAM HELPER (DUAL FALLBACK)")
    draw_lane_hdr(66.6, 100, "TELEGRAM BOT API (CLOUD)")

    def draw_action(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.6,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    def draw_decision(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.8], [cx + 3.8, cy], [cx, cy - 2.8], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 4.2, label, ha='center', va='top', fontsize=7.5, color='#B45309', fontweight='bold')
        return (cx, cy)

    def arrow(x1, y1, x2, y2, label=""):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.3, mutation_scale=12))
        if label:
            mx, my = (x1 + x2)/2, (y1 + y2)/2
            ax.text(mx + 1.8, my, label, fontsize=7.2, color="#0F172A", fontweight='bold')

    c_start = patches.Circle((16.6, 84), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    draw_action(16.6, 75, 26, 5.5, "Data Presensi Siswa/Guru\nBerhasil Tersimpan di DB")
    arrow(16.6, 82.2, 16.6, 77.8)

    draw_action(50.0, 75, 26, 5.5, "Panggil Helper Notifikasi\n(Cek Status Token & Chat ID)")
    arrow(29.6, 75, 37.0, 75)

    draw_action(50.0, 63, 26, 5.5, "Format Template Pesan HTML\n(Nama, NIP/NIS, Jam, Status)")
    arrow(50.0, 72.2, 50.0, 65.8)

    draw_decision(50.0, 51, "Ekstensi cURL Aktif?")
    arrow(50.0, 60.2, 50.0, 53.8)

    draw_action(83.3, 51, 27, 5.5, "Eksekusi cURL POST HTTPS\n(Timeout 8 Detik)")
    arrow(53.8, 51, 69.8, 51, "[Ya]")

    draw_action(50.0, 37, 26, 5.5, "Fallback: PHP Stream Context\n(file_get_contents, 10s)", bg='#FEF3C7', border='#D97706')
    arrow(50.0, 48.2, 50.0, 39.8, "[Tidak/Gagal]")

    arrow(50.0, 34.2, 69.8, 25)

    draw_action(83.3, 25, 27, 5.5, "Telegram API Server Memproses\n& Kirim ke Chat ID Target")
    arrow(83.3, 48.2, 83.3, 27.8)

    draw_action(50.0, 15, 26, 5.0, "Catat Error Log Safe\n(Tanpa Bocor Token Rahasia)", bg='#FEE2E2', border='#DC2626')

    draw_action(16.6, 15, 26, 5.5, "Notifikasi Masuk ke HP\nPihak Sekolah / Orang Tua", bg='#DCFCE7', border='#16A34A')
    arrow(69.8, 25, 29.6, 15, "[Sukses HTTP 200]")

    def draw_end(cx, cy):
        c1 = patches.Circle((cx, cy), 2.2, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
        c2 = patches.Circle((cx, cy), 1.3, facecolor='#0F172A', edgecolor='#0F172A')
        ax.add_patch(c1)
        ax.add_patch(c2)

    draw_end(16.6, 4.5)
    arrow(16.6, 12.2, 16.6, 6.7)

    plt.tight_layout()
    out_path = os.path.join(out_dir, "activity_diagram_telegram.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram Telegram saved.")

# ══════════════════════════════════════════════════════════════════════════════
# 5. CLASS DIAGRAM (UML SOLID OBJECT-ORIENTED DESIGN)
# ══════════════════════════════════════════════════════════════════════════════
def draw_class_diagram():
    fig, ax = plt.subplots(figsize=(13, 10), dpi=300)
    ax.set_xlim(0, 130)
    ax.set_ylim(0, 100)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    def draw_class_box(cx, cy, w, h, name, attrs, methods, is_interface=False, header_bg='#1B365D'):
        x = cx - w/2
        y = cy - h/2
        rect = patches.FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.3,rounding_size=0.5",
                                      facecolor='#FFFFFF', edgecolor=header_bg, lw=1.5)
        ax.add_patch(rect)
        hdr_rect = patches.Rectangle((x, y + h - 5.5), w, 5.5, facecolor=header_bg, edgecolor=header_bg)
        ax.add_patch(hdr_rect)
        
        prefix = "<<interface>>\n" if is_interface else ""
        ax.text(cx, y + h - 2.8, f"{prefix}{name}", ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        
        # Attributes
        curr_y = y + h - 7.5
        for a in attrs:
            ax.text(x + 1.2, curr_y, a, ha='left', va='center', color='#334155', fontsize=7.2, fontfamily='monospace')
            curr_y -= 2.6
            
        # Divider line
        curr_y -= 0.8
        ax.plot([x, x + w], [curr_y, curr_y], color='#CBD5E1', lw=1)
        curr_y -= 2.2
        
        # Methods
        for m in methods:
            ax.text(x + 1.2, curr_y, m, ha='left', va='center', color='#1E293B', fontsize=7.2, fontfamily='monospace', fontweight='semibold')
            curr_y -= 2.6
        return (cx, cy)

    # 1. Controller Base
    draw_class_box(20, 85, 26, 18, "Controller", 
                   ["# db: Database"], 
                   ["+ view(view, data)", "+ model(model)"])

    # 2. Absensi_siswa Controller
    draw_class_box(20, 52, 28, 22, "Absensi_siswa",
                   ["- model: Absensi_model"],
                   ["+ index()", "+ scan()", "+ validasiRadius()", "+ catatPresensi()"])

    # 3. Database Core
    draw_class_box(62, 85, 30, 22, "Database",
                   ["- dbHandler: PDO", "- stmt: PDOStatement"],
                   ["+ query(sql)", "+ bind(param, val)", "+ execute(): bool", "+ resultSet(): array", "+ single(): array"])

    # 4. Absensi_model
    draw_class_box(62, 50, 32, 28, "Absensi_model",
                   ["- table: string", "- db: Database"],
                   ["+ evaluasiWaktuJadwal()", "+ catatAbsensiSiswaBarcode()", "+ catatAbsensiGuruBarcode()", "+ kirimNotifikasiTelegramSafe()"])

    # 5. AiProviderInterface
    draw_class_box(108, 85, 34, 20, "AiProviderInterface",
                   [],
                   ["+ sendMessage(p, c): array", "+ testConnection(): array"],
                   is_interface=True, header_bg='#047857')

    # 6. AiProviderFactory
    draw_class_box(108, 55, 32, 18, "AiProviderFactory",
                   [],
                   ["+ create(config): AiProvider"])

    # 7. GeminiAdapter
    draw_class_box(92, 22, 28, 22, "GeminiAdapter",
                   ["- apiKey: string", "- model: string", "- baseUrl: string"],
                   ["+ sendMessage()", "+ testConnection()"],
                   header_bg='#2563EB')

    # 8. OpenAiAdapter
    draw_class_box(122, 22, 28, 22, "OpenAiAdapter",
                   ["- apiKey: string", "- model: string", "- baseUrl: string"],
                   ["+ sendMessage()", "+ testConnection()"],
                   header_bg='#2563EB')

    # 9. TelegramHelper
    draw_class_box(20, 20, 28, 22, "TelegramHelper",
                   ["- token: string", "- chatId: string"],
                   ["+ isEnabled(): bool", "+ kirimPesan(text): array", "+ notifikasiAbsensiSiswa()", "+ notifikasiAbsensiGuru()"],
                   header_bg='#D97706')

    # 10. LokasiHelper
    draw_class_box(56, 18, 30, 20, "LokasiHelper",
                   ["- latSekolah: float", "- lngSekolah: float"],
                   ["+ hitungJarakMeter()", "+ validasiJarak(): array"],
                   header_bg='#D97706')

    # Arrows & Associations
    def connect_arrow(x1, y1, x2, y2, ls='-', color='#475569'):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.3, ls=ls, mutation_scale=10))

    # Controller -> Absensi_siswa (Inheritance)
    connect_arrow(20, 63, 20, 76)
    # Absensi_siswa -> Absensi_model (Dependency)
    connect_arrow(34, 52, 46, 52)
    # Absensi_model -> Database
    connect_arrow(62, 64, 62, 74)
    # Absensi_model -> TelegramHelper
    connect_arrow(50, 40, 34, 25, ls='--')
    # Absensi_siswa -> LokasiHelper
    connect_arrow(25, 41, 44, 24, ls='--')
    # AiProviderFactory -> AiProviderInterface
    connect_arrow(108, 64, 108, 75, ls='--')
    # GeminiAdapter -> AiProviderInterface (Implements)
    connect_arrow(92, 33, 98, 75, ls='--')
    # OpenAiAdapter -> AiProviderInterface (Implements)
    connect_arrow(122, 33, 118, 75, ls='--')

    plt.tight_layout()
    out_path = os.path.join(out_dir, "class_diagram.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Class Diagram saved.")

# ══════════════════════════════════════════════════════════════════════════════
# 6. SEQUENCE DIAGRAM: PRESENSI & GEOFENCING
# ══════════════════════════════════════════════════════════════════════════════
def draw_sequence_presensi():
    fig, ax = plt.subplots(figsize=(12, 10), dpi=300)
    ax.set_xlim(0, 120)
    ax.set_ylim(0, 105)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # Lifeline entities
    lifelines = [
        (12, "User\n(Siswa/Guru)"),
        (32, "Web Scanner\n(Frontend JS)"),
        (54, "Absensi Controller\n(Backend PHP)"),
        (76, "LokasiHelper &\nJadwal_model"),
        (98, "Absensi_model &\nMySQL Database"),
        (116, "Telegram\nBot API")
    ]

    for x, name in lifelines:
        box = patches.FancyBboxPatch((x - 8, 92), 16, 7, boxstyle="round,pad=0.3",
                                     facecolor='#1B365D', edgecolor='#0F172A', lw=1.2)
        ax.add_patch(box)
        ax.text(x, 95.5, name, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=7.8)
        # Lifeline dotted
        ax.plot([x, x], [92, 8], color='#94A3B8', linestyle='--', lw=1.2)

    def msg(x1, x2, y, label, is_return=False, color='#1E293B'):
        ls = '--' if is_return else '-'
        ax.annotate("", xy=(x2, y), xytext=(x1, y),
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.2, ls=ls, mutation_scale=10))
        mx = (x1 + x2)/2
        ax.text(mx, y + 1.2, label, ha='center', va='bottom', fontsize=7.2, color=color, fontweight='bold')

    # Alur sequence
    msg(12, 32, 86, "1. Pindai Barcode Kamera / Tap RFID")
    msg(32, 32, 81, "2. Ambil Koordinat GPS Browser")
    msg(32, 54, 76, "3. POST AJAX /absensi_siswa/scan")
    msg(54, 76, 70, "4. validasiJarak(userLat, userLng)")
    msg(76, 54, 65, "5. Jarak: 35 meter (Valid <= 100m)", is_return=True, color='#16A34A')
    msg(54, 76, 60, "6. getActiveJadwal('siswa', 'Sabtu')")
    msg(76, 54, 55, "7. Jadwal Ditemukan (06:00 - 16:00)", is_return=True, color='#16A34A')
    msg(54, 98, 49, "8. evaluasiWaktuJadwal() -> Status: Hadir")
    msg(54, 98, 44, "9. INSERT INTO absensi_siswa (...)")
    msg(98, 54, 39, "10. Status: Berhasil Disimpan", is_return=True)
    msg(54, 116, 33, "11. notifikasiAbsensiSiswa(data)")
    msg(116, 54, 28, "12. HTTP 200 OK (Notif Terkirim)", is_return=True, color='#16A34A')
    msg(54, 32, 22, "13. JSON {status: true, pesan: 'Berhasil'}", is_return=True)
    msg(32, 12, 16, "14. Tampilkan Kartu Presensi & Suara Beep", is_return=True, color='#16A34A')

    plt.tight_layout()
    out_path = os.path.join(out_dir, "sequence_diagram_presensi.png")
    plt.savefig(out_path, dpi=300, bbox_inches='tight')
    plt.close()
    print("Sequence Diagram Presensi saved.")

if __name__ == "__main__":
    draw_use_case()
    draw_activity_presensi()
    draw_activity_ai()
    draw_activity_telegram()
    draw_class_diagram()
    draw_sequence_presensi()
    print("ALL 6 DIAGRAMS GENERATED WITH HIGH RESOLUTION!")
