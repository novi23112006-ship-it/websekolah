import os
import matplotlib.pyplot as plt
import matplotlib.patches as patches
import numpy as np

out_dir = r"C:\xampp1\htdocs\websekolah3\laporan word"
os.makedirs(out_dir, exist_ok=True)

plt.rcParams['font.sans-serif'] = 'Arial'
plt.rcParams['axes.edgecolor'] = '#CCCCCC'

# ==============================================================================
# 1. USE CASE DIAGRAM (STANDARD UML 2.5 - ZERO SPAGHETTI)
# ==============================================================================
def draw_perfect_use_case():
    fig, ax = plt.subplots(figsize=(19, 17), dpi=300)
    ax.set_xlim(0, 185)
    ax.set_ylim(0, 168)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # System Boundary Rectangle
    sb_x, sb_y, sb_w, sb_h = 36, 6, 144, 156
    boundary = patches.Rectangle((sb_x, sb_y), sb_w, sb_h,
                                 edgecolor='#1E293B', facecolor='#F8FAFC', linewidth=2.0)
    ax.add_patch(boundary)

    # Boundary Header Box (Clean classic UML title)
    ax.text(sb_x + 6, sb_y + sb_h - 5, 'Sistem Informasi Presensi & Monitoring Absensi SMK (Berbasis Web)',
            fontsize=12.5, fontweight='bold', color='#0F172A',
            bbox=dict(boxstyle='square,pad=0.45', facecolor='#FFFFFF', edgecolor='#CBD5E1', lw=1.2))

    # Stick figure drawer
    def draw_actor(x, y, name, role=''):
        # Head
        head = patches.Circle((x, y + 5.5), 2.3, edgecolor='#0F172A', facecolor='#FFFFFF', lw=2.0)
        ax.add_patch(head)
        # Body
        ax.plot([x, x], [y + 3.2, y - 4.0], color='#0F172A', lw=2.2, solid_capstyle='round')
        # Arms
        ax.plot([x - 4.2, x + 4.2], [y + 1.0, y + 1.0], color='#0F172A', lw=2.2, solid_capstyle='round')
        # Legs
        ax.plot([x, x - 3.5], [y - 4.0, y - 9.5], color='#0F172A', lw=2.2, solid_capstyle='round')
        ax.plot([x, x + 3.5], [y - 4.0, y - 9.5], color='#0F172A', lw=2.2, solid_capstyle='round')
        # Name label
        ax.text(x, y - 11.8, name, ha='center', va='top', fontsize=10.5, fontweight='bold', color='#0F172A')
        if role:
            ax.text(x, y - 14.5, role, ha='center', va='top', fontsize=8.2, fontstyle='italic', color='#475569')

    # Use case drawer (classic light yellow / cream oval)
    use_cases = {}
    def draw_uc(id_key, cx, cy, w, h, text, is_sub=False):
        edge_col = '#854D0E' if not is_sub else '#0369A1'
        face_col = '#FEF9C3' if not is_sub else '#E0F2FE'
        el = patches.Ellipse((cx, cy), w, h, edgecolor=edge_col, facecolor=face_col, lw=1.6)
        ax.add_patch(el)
        lines = text.split('\n')
        if len(lines) == 1:
            ax.text(cx, cy, lines[0], ha='center', va='center', fontsize=8.5, fontweight='bold', color='#0F172A')
        elif len(lines) == 2:
            ax.text(cx, cy + 0.9, lines[0], ha='center', va='center', fontsize=8.4, fontweight='bold', color='#0F172A')
            ax.text(cx, cy - 1.1, lines[1], ha='center', va='center', fontsize=8.4, fontweight='bold', color='#0F172A')
        elif len(lines) == 3:
            ax.text(cx, cy + 1.6, lines[0], ha='center', va='center', fontsize=8.0, fontweight='bold', color='#0F172A')
            ax.text(cx, cy, lines[1], ha='center', va='center', fontsize=8.0, fontweight='bold', color='#0F172A')
            ax.text(cx, cy - 1.6, lines[2], ha='center', va='center', fontsize=8.0, fontweight='bold', color='#0F172A')
        use_cases[id_key] = (cx, cy, w, h)

    # 5 Human Actors on the Left (x = 18)
    draw_actor(18, 142, 'Super Admin', '(Administrator)')
    draw_actor(18, 112, 'Kepala Sekolah', '(Pimpinan Sekolah)')
    draw_actor(18, 86,  'Wali Kelas', '(Guru Pembimbing)')
    draw_actor(18, 56,  'Dewan Guru', '(Tenaga Pendidik)')
    draw_actor(18, 22,  'Siswa', '(Peserta Didik)')

    # Column 1 (Primary Functional Use Cases) at cx = 72, w = 36, h = 6.2
    col1_cx = 72
    draw_uc('UC_LOGIN',   col1_cx, 154, 36, 6.2, 'Login & Autentikasi Sistem\n(Bcrypt Hashing)')
    draw_uc('UC_USER',    col1_cx, 144, 36, 6.2, 'Kelola Data Pengguna\n(Guru, Siswa, Admin)')
    draw_uc('UC_JADWAL',  col1_cx, 134, 36, 6.2, 'Kelola Kelas &\nJadwal Pembelajaran')
    draw_uc('UC_RADIUS',  col1_cx, 124, 36, 6.2, 'Pengaturan Radius\nGeofencing (100 Meter)')
    draw_uc('UC_APIKEY',  col1_cx, 114, 36, 6.2, 'Manajemen API Key\nMulti-Provider AI')
    draw_uc('UC_MON_KEP', col1_cx, 104, 36, 6.2, 'Monitoring Presensi\nEksekutif Sekolah')
    draw_uc('UC_REKAP',   col1_cx, 94,  36, 6.2, 'Cetak & Unduh Rekap\nLaporan Presensi (PDF/Excel)')
    draw_uc('UC_MON_WAL', col1_cx, 84,  36, 6.2, 'Monitoring Presensi\nSiswa Kelas Binaan')
    draw_uc('UC_DISIPLIN',col1_cx, 73,  36, 6.2, 'Kelola Catatan\nKedisiplinan Siswa')
    draw_uc('UC_SCAN_G',  col1_cx, 61,  36, 6.2, 'Presensi Barcode / QR\nScanner Kamera (Guru)')
    draw_uc('UC_SCAN_S',  col1_cx, 49,  36, 6.2, 'Presensi Mandiri Siswa\n(Scan Kartu RFID/Barcode)')
    draw_uc('UC_IZIN',    col1_cx, 37,  36, 6.2, 'Pengajuan Surat Izin &\nSakit Online (Upload Bukti)')
    draw_uc('UC_CHATBOT', col1_cx, 21,  36, 6.2, 'Konsultasi Asisten\nVirtual AI (Chatbot)')

    # Column 2 (Sub-processes, <<include>> & <<extend>>) at cx = 142, w = 38, h = 6.2
    col2_cx = 142
    draw_uc('SUB_CARD',   col2_cx, 144, 38, 6.2, 'Registrasi & Generate\nKartu Barcode / RFID', is_sub=True)
    draw_uc('SUB_GPS',    col2_cx, 70,  38, 6.2, 'Validasi Radius Koordinat\nGPS (Formula Haversine)', is_sub=True)
    draw_uc('SUB_JAM',    col2_cx, 56,  38, 6.2, 'Verifikasi Jadwal &\nJam Operasional Sekolah', is_sub=True)
    draw_uc('SUB_NOTIF',  col2_cx, 42,  38, 6.2, 'Kirim Notifikasi Otomatis\nTelegram Bot API', is_sub=True)
    draw_uc('SUB_INJECT', col2_cx, 21,  38, 6.2, 'Injeksi Konteks Data\nSekolah Dinamis ke AI', is_sub=True)

    # Function to connect actor to use case with clean arrow
    def connect_actor(act_x, act_y, uc_id):
        cx, cy, w, h = use_cases[uc_id]
        target_x = cx - w/2
        target_y = cy
        start_x = act_x + 4.5
        start_y = act_y
        ax.annotate('', xy=(target_x, target_y), xytext=(start_x, start_y),
                    arrowprops=dict(arrowstyle='->', color='#1E293B', lw=1.6, mutation_scale=11))

    # 1. Super Admin (18, 142)
    connect_actor(18, 142, 'UC_LOGIN')
    connect_actor(18, 142, 'UC_USER')
    connect_actor(18, 142, 'UC_JADWAL')
    connect_actor(18, 142, 'UC_RADIUS')
    connect_actor(18, 142, 'UC_APIKEY')

    # 2. Kepala Sekolah (18, 112)
    connect_actor(18, 112, 'UC_MON_KEP')
    connect_actor(18, 112, 'UC_REKAP')

    # 3. Wali Kelas (18, 86)
    connect_actor(18, 86, 'UC_MON_WAL')
    connect_actor(18, 86, 'UC_DISIPLIN')
    connect_actor(18, 86, 'UC_REKAP')

    # 4. Dewan Guru (18, 56)
    connect_actor(18, 56, 'UC_SCAN_G')
    connect_actor(18, 56, 'UC_IZIN')
    connect_actor(18, 56, 'UC_CHATBOT')

    # 5. Siswa (18, 22)
    connect_actor(18, 22, 'UC_SCAN_S')
    connect_actor(18, 22, 'UC_IZIN')
    connect_actor(18, 22, 'UC_CHATBOT')

    # UML Include & Extend Relationships
    def draw_relationship(from_id, to_id, rel_type='include', note=''):
        fcx, fcy, fw, fh = use_cases[from_id]
        tcx, tcy, tw, th = use_cases[to_id]
        
        if rel_type == 'include':
            start_x = fcx + fw/2
            start_y = fcy
            end_x = tcx - tw/2
            end_y = tcy
            col = '#B91C1C'
            label = '«include»'
            ax.annotate('', xy=(end_x, end_y), xytext=(start_x, start_y),
                        arrowprops=dict(arrowstyle='->', color=col, lw=1.5, ls='--', mutation_scale=12))
            mid_x = (start_x + end_x) / 2
            mid_y = (start_y + end_y) / 2
            ax.text(mid_x, mid_y + 1.2, label, ha='center', va='center', fontsize=7.8,
                    color=col, fontweight='bold', bbox=dict(boxstyle='round,pad=0.15', facecolor='#FFFFFF', edgecolor=col, lw=0.7))
        elif rel_type == 'extend':
            start_x = tcx - tw/2
            start_y = tcy
            end_x = fcx + fw/2
            end_y = fcy
            col = '#15803D'
            label = f'«extend»\n{note}' if note else '«extend»'
            ax.annotate('', xy=(end_x, end_y), xytext=(start_x, start_y),
                        arrowprops=dict(arrowstyle='->', color=col, lw=1.5, ls='--', mutation_scale=12))
            mid_x = (start_x + end_x) / 2
            mid_y = (start_y + end_y) / 2
            ax.text(mid_x, mid_y + 1.2, label, ha='center', va='center', fontsize=7.2,
                    color=col, fontweight='bold', bbox=dict(boxstyle='round,pad=0.15', facecolor='#FFFFFF', edgecolor=col, lw=0.7))

    draw_relationship('UC_USER', 'SUB_CARD', 'include')
    draw_relationship('UC_SCAN_G', 'SUB_GPS', 'include')
    draw_relationship('UC_SCAN_S', 'SUB_GPS', 'include')
    draw_relationship('UC_SCAN_G', 'SUB_JAM', 'include')
    draw_relationship('UC_SCAN_S', 'SUB_JAM', 'include')
    draw_relationship('UC_SCAN_G', 'SUB_NOTIF', 'extend', '[Presensi Sukses]')
    draw_relationship('UC_SCAN_S', 'SUB_NOTIF', 'extend', '[Presensi Sukses]')
    draw_relationship('UC_CHATBOT', 'SUB_INJECT', 'include')

    # Legend at bottom right
    leg_x, leg_y = 100, 10
    ax.text(leg_x, leg_y, 'Keterangan Relasi UML:\n───> : Asosiasi Aktor / Inisiasi Use Case\n- - - > «include» : Eksekusi Wajib Sub-Proses\n- - - > «extend»  : Perluasan Fungsional Bersyarat',
            fontsize=8.0, color='#334155',
            bbox=dict(boxstyle='round,pad=0.4', facecolor='#FFFFFF', edgecolor='#94A3B8', lw=1.0))

    plt.tight_layout()
    
    for base in [r"C:\xampp\htdocs\websekolah3\laporan word", r"C:\xampp1\htdocs\websekolah3\laporan word"]:
        if os.path.exists(base):
            out_png = os.path.join(base, "use_case_diagram.png")
            plt.savefig(out_png, dpi=300, bbox_inches='tight')
            print(f"Generated successfully: {out_png}")
            
    plt.close()

# ==============================================================================
# 2. ACTIVITY DIAGRAM 1: PRESENSI GEOFENCING & EVALUASI JADWAL (STRICT ORTHOGONAL)
# ==============================================================================
def draw_perfect_activity_presensi():
    fig, ax = plt.subplots(figsize=(13, 15), dpi=300)
    ax.set_xlim(0, 120)
    ax.set_ylim(0, 145)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # 4 Swimlanes: User (0-30), Frontend (30-60), Backend (60-90), Database & Telegram (90-120)
    lanes = [
        (0, 30, "AKTOR: PENGGUNA\n(SISWA / GURU)"),
        (30, 60, "FRONTEND: WEB BROWSER\n(KAMERA & GPS API)"),
        (60, 90, "BACKEND: CONTROLLER\n(ABSENSI ENGINE)"),
        (90, 120, "DATABASE & EXTERNAL\n(MYSQL & TELEGRAM)")
    ]

    for x1, x2, title in lanes:
        # Lane Header
        rect = patches.Rectangle((x1, 137), x2 - x1, 7, facecolor='#1E3A8A', edgecolor='#0F172A', lw=1.2)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 140.5, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        # Vertical divider line
        ax.plot([x2, x2], [2, 137], color='#94A3B8', lw=1.2, ls='--')

    # Helper: Draw Action State
    def draw_act(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.5,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=7.8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    # Helper: Draw Decision Diamond
    def draw_dec(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.5], [cx + 3.8, cy], [cx, cy - 2.5], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 3.8, label, ha='center', va='top', fontsize=7.2, color='#B45309', fontweight='bold')
        return (cx, cy)

    # Helper: Strictly Orthogonal Arrow
    def ortho_arrow(pts, label="", lbl_pos='mid', lbl_color='#0F172A'):
        # pts is list of (x, y) tuples
        for i in range(len(pts) - 1):
            x1, y1 = pts[i]
            x2, y2 = pts[i+1]
            if i == len(pts) - 2:
                # Last segment with arrowhead
                ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                            arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.4, mutation_scale=12))
            else:
                ax.plot([x1, x2], [y1, y2], color="#1E293B", lw=1.4)
        if label:
            mid_pt = pts[len(pts)//2]
            ax.text(mid_pt[0] + 1.2, mid_pt[1], label, fontsize=7.2, color=lbl_color, fontweight='bold')

    # 1. Start Node
    c_start = patches.Circle((15, 133), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    # Step 1: Open Scanner (User)
    draw_act(15, 124, 25, 5.2, "Buka Menu Scanner Presensi\n(Kamera Barcode / RFID)")
    ortho_arrow([(15, 131.2), (15, 126.6)])

    # Step 2: Init GPS (Frontend)
    draw_act(45, 124, 25, 5.2, "Inisialisasi Kamera &\nRequest Akses GPS Geolocation")
    ortho_arrow([(27.5, 124), (32.5, 124)])

    # Step 3: Get Lat Lng (Frontend)
    draw_act(45, 112, 25, 5.2, "Dapatkan Titik Koordinat\n(Latitude & Longitude)")
    ortho_arrow([(45, 121.4), (45, 114.6)])

    # Step 4: Scan ID (User)
    draw_act(15, 112, 25, 5.2, "Pindai Barcode ID Guru /\nTap Kartu RFID Siswa")
    ortho_arrow([(32.5, 112), (27.5, 112)])

    # Step 5: Send AJAX (Frontend)
    draw_act(45, 100, 25, 5.2, "Kirim Payload AJAX POST\n(Barcode, Lat, Lng, CSRF)")
    ortho_arrow([(15, 109.4), (15, 100), (32.5, 100)])

    # Step 6: Haversine Calculation (Backend)
    draw_act(75, 100, 26, 5.2, "Hitung Jarak Koordinat Sekolah\n(Formula Haversine Spherical)")
    ortho_arrow([(57.5, 100), (62.0, 100)])

    # Step 7: Decision Radius (Backend)
    draw_dec(75, 88, "Jarak <= 100m?")
    ortho_arrow([(75, 97.4), (75, 90.5)])

    # Fail Radius (Frontend)
    draw_act(45, 88, 25, 5.2, "Tolak Presensi:\nDi Luar Radius Sekolah", bg='#FEE2E2', border='#DC2626')
    ortho_arrow([(71.2, 88), (57.5, 88)], label="[Tidak]", lbl_color='#DC2626')

    # Step 8: Check Schedule (Backend)
    draw_act(75, 76, 26, 5.2, "Evaluasi Jadwal Hari Ini\n(Model Jadwal_absensi)")
    ortho_arrow([(75, 85.5), (75, 78.6)], label="[Ya]", lbl_color='#16A34A')

    # DB Query Schedule (Database)
    draw_act(105, 76, 25, 5.2, "SELECT Jadwal Aktif\n(Berdasarkan Hari & Role)")
    ortho_arrow([(88.0, 76), (92.5, 76)])
    ortho_arrow([(105, 73.4), (105, 65), (88.0, 65)])

    # Step 9: Decision Schedule Found (Backend)
    draw_dec(75, 65, "Jadwal Aktif?")

    # Fail Schedule (Frontend)
    draw_act(45, 65, 25, 5.2, "Tolak: Jadwal Hari Ini\nBelum Dikonfigurasi", bg='#FEE2E2', border='#DC2626')
    ortho_arrow([(71.2, 65), (57.5, 65)], label="[Tidak]", lbl_color='#DC2626')

    # Step 10: Decision Ontime vs Late (Backend)
    draw_dec(75, 52, "Jam <= Batas Masuk?")
    ortho_arrow([(75, 62.5), (75, 54.5)], label="[Ya]", lbl_color='#16A34A')

    # Branch Status
    draw_act(66, 40, 16, 4.6, "Status = 'Hadir'\n(Tepat Waktu)", bg='#DCFCE7', border='#16A34A')
    draw_act(84, 40, 16, 4.6, "Status = 'Terlambat'\n(Lewat Jam)", bg='#FEF3C7', border='#D97706')

    ortho_arrow([(71.2, 52), (66, 52), (66, 42.3)], label="[Ya]", lbl_color='#16A34A')
    ortho_arrow([(78.8, 52), (84, 52), (84, 42.3)], label="[Tidak]", lbl_color='#D97706')

    # Step 11: Save Record (Backend)
    draw_act(75, 28, 26, 5.0, "Simpan Record Presensi Harian\n(absensi_siswa / absensi_guru)")
    ortho_arrow([(66, 37.7), (66, 32), (75, 32), (75, 30.5)])
    ortho_arrow([(84, 37.7), (84, 32), (75, 32), (75, 30.5)])

    # DB Insert & Telegram Trigger (Database & External)
    draw_act(105, 28, 25, 5.0, "INSERT Database &\nTrigger Telegram Bot Helper")
    ortho_arrow([(88.0, 28), (92.5, 28)])

    # Step 12: Success Notification (Frontend)
    draw_act(45, 17, 25, 5.0, "Tampilkan Kartu Presensi\n& Notifikasi SweetAlert2 Sukses", bg='#DCFCE7', border='#16A34A')
    ortho_arrow([(75, 25.5), (75, 17), (57.5, 17)])

    # Step 13: View Confirmation (User)
    draw_act(15, 17, 25, 5.0, "Melihat Hasil Presensi\n& Status Kehadiran Terverifikasi")
    ortho_arrow([(32.5, 17), (27.5, 17)])

    # Merge Node
    d_merge = patches.Polygon([[15, 9.5], [17.5, 7.5], [15, 5.5], [12.5, 7.5]],
                              facecolor='#E2E8F0', edgecolor='#475569', lw=1.2)
    ax.add_patch(d_merge)

    ortho_arrow([(15, 14.5), (15, 9.5)])
    ortho_arrow([(32.5, 88), (6, 88), (6, 7.5), (12.5, 7.5)])
    ortho_arrow([(32.5, 65), (8, 65), (8, 7.5), (12.5, 7.5)])

    # Activity Final Node
    c1 = patches.Circle((15, 2.5), 1.8, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
    c2 = patches.Circle((15, 2.5), 1.1, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c1)
    ax.add_patch(c2)
    ortho_arrow([(15, 5.5), (15, 4.3)])

    plt.tight_layout()
    out_file = os.path.join(out_dir, "activity_diagram_presensi.png")
    if os.path.exists(out_file):
        os.remove(out_file)
    plt.savefig(out_file, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram Presensi generated successfully.")

# ==============================================================================
# 3. ACTIVITY DIAGRAM 2: CHATBOT AI ASSISTANT (STRICT ORTHOGONAL)
# ==============================================================================
def draw_perfect_activity_ai():
    fig, ax = plt.subplots(figsize=(13, 13), dpi=300)
    ax.set_xlim(0, 120)
    ax.set_ylim(0, 125)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    lanes = [
        (0, 30, "PENGGUNA\n(DASHBOARD)"),
        (30, 60, "FRONTEND WIDGET\n(CHATBOT AJAX CLIENT)"),
        (60, 90, "BACKEND CONTROLLER\n(CONTEXT INJECTOR)"),
        (90, 120, "AI PROVIDER API\n(GEMINI / OPENAI CLOUD)")
    ]

    for x1, x2, title in lanes:
        rect = patches.Rectangle((x1, 118), x2 - x1, 6.5, facecolor='#1E3A8A', edgecolor='#0F172A', lw=1.2)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 121.2, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        ax.plot([x2, x2], [2, 118], color='#94A3B8', lw=1.2, ls='--')

    def draw_act(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.5,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=7.8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    def draw_dec(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.5], [cx + 3.8, cy], [cx, cy - 2.5], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 3.8, label, ha='center', va='top', fontsize=7.2, color='#B45309', fontweight='bold')
        return (cx, cy)

    def ortho_arrow(pts, label="", lbl_color='#0F172A'):
        for i in range(len(pts) - 1):
            x1, y1 = pts[i]
            x2, y2 = pts[i+1]
            if i == len(pts) - 2:
                ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                            arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.4, mutation_scale=12))
            else:
                ax.plot([x1, x2], [y1, y2], color="#1E293B", lw=1.4)
        if label:
            mid_pt = pts[len(pts)//2]
            ax.text(mid_pt[0] + 1.2, mid_pt[1], label, fontsize=7.2, color=lbl_color, fontweight='bold')

    # Start
    c_start = patches.Circle((15, 114), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    draw_act(15, 105, 25, 5.2, "Klik Widget Chatbot &\nKetik Pertanyaan")
    ortho_arrow([(15, 112.2), (15, 107.6)])

    draw_act(45, 105, 25, 5.2, "Sanitasi Input Frontend &\nKirim Request AJAX POST")
    ortho_arrow([(27.5, 105), (32.5, 105)])

    draw_act(75, 105, 26, 5.2, "Validasi CSRF Token &\nSesi Login Pengguna")
    ortho_arrow([(57.5, 105), (62.0, 105)])

    draw_act(75, 92, 26, 5.2, "Inject Konteks Data Sekolah\n(Jadwal, Presensi, Mapel)")
    ortho_arrow([(75, 102.4), (75, 94.6)])

    draw_act(75, 80, 26, 5.2, "Ambil Konfigurasi AI Aktif\n(Ai_model::getActiveConfig)")
    ortho_arrow([(75, 89.4), (75, 82.6)])

    draw_act(75, 68, 26, 5.2, "Instansiasi Adapter Provider\n(GeminiAdapter / OpenAi)")
    ortho_arrow([(75, 77.4), (75, 70.6)])

    draw_act(105, 68, 25, 5.2, "Kirim Payload JSON HTTPS\n(Prompt + System Prompt)")
    ortho_arrow([(88.0, 68), (92.5, 68)])

    draw_dec(105, 54, "HTTP 200 OK?")
    ortho_arrow([(105, 65.4), (105, 56.5)])

    draw_act(105, 40, 25, 5.0, "Ekstraksi Teks Balasan AI\n(Parsing Format Markdown)", bg='#DCFCE7', border='#16A34A')
    ortho_arrow([(105, 51.5), (105, 42.5)], label="[Ya]", lbl_color='#16A34A')

    draw_act(75, 54, 26, 5.0, "Fallback: Coba Kunci Cadangan /\nPesan Maaf Tanpa Bocor Key", bg='#FEF3C7', border='#D97706')
    ortho_arrow([(101.2, 54), (88.0, 54)], label="[Gagal/Limit]", lbl_color='#DC2626')

    draw_act(45, 26, 25, 5.2, "Render Balasan Chatbot\ndi Gelembung Percakapan UI", bg='#DCFCE7', border='#16A34A')
    ortho_arrow([(105, 37.5), (105, 26), (57.5, 26)])
    ortho_arrow([(75, 51.5), (75, 36), (45, 36), (45, 28.6)])

    draw_act(15, 26, 25, 5.2, "Membaca Jawaban Asisten AI\n& Melanjutkan Konsultasi")
    ortho_arrow([(32.5, 26), (27.5, 26)])

    c1 = patches.Circle((15, 12), 1.8, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
    c2 = patches.Circle((15, 12), 1.1, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c1)
    ax.add_patch(c2)
    ortho_arrow([(15, 23.4), (15, 13.8)])

    plt.tight_layout()
    out_file = os.path.join(out_dir, "activity_diagram_ai.png")
    if os.path.exists(out_file):
        os.remove(out_file)
    plt.savefig(out_file, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram AI generated successfully.")

# ==============================================================================
# 4. ACTIVITY DIAGRAM 3: NOTIFIKASI REAL-TIME TELEGRAM (STRICT ORTHOGONAL)
# ==============================================================================
def draw_perfect_activity_telegram():
    fig, ax = plt.subplots(figsize=(12, 12), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 115)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    lanes = [
        (0, 33.3, "TRANSAKSI PRESENSI\n(EVENT ENGINE)"),
        (33.3, 66.6, "TELEGRAM HELPER\n(DUAL-FALLBACK CLIENT)"),
        (66.6, 100, "TELEGRAM CLOUD SERVER\n(BOT API GATEWAY)")
    ]

    for x1, x2, title in lanes:
        rect = patches.Rectangle((x1, 107), x2 - x1, 6.5, facecolor='#1E3A8A', edgecolor='#0F172A', lw=1.2)
        ax.add_patch(rect)
        ax.text((x1 + x2)/2, 110.2, title, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        ax.plot([x2, x2], [2, 107], color='#94A3B8', lw=1.2, ls='--')

    def draw_act(cx, cy, w, h, text, bg='#EFF6FF', border='#2563EB'):
        r = patches.FancyBboxPatch((cx - w/2, cy - h/2), w, h, boxstyle="round,pad=0.5,rounding_size=0.8",
                                  facecolor=bg, edgecolor=border, lw=1.5)
        ax.add_patch(r)
        ax.text(cx, cy, text, ha='center', va='center', fontsize=7.8, color='#0F172A', fontweight='bold')
        return (cx, cy)

    def draw_dec(cx, cy, label=""):
        d = patches.Polygon([[cx, cy + 2.5], [cx + 3.8, cy], [cx, cy - 2.5], [cx - 3.8, cy]],
                            facecolor='#FEF3C7', edgecolor='#D97706', lw=1.5)
        ax.add_patch(d)
        if label:
            ax.text(cx, cy - 3.8, label, ha='center', va='top', fontsize=7.2, color='#B45309', fontweight='bold')
        return (cx, cy)

    def ortho_arrow(pts, label="", lbl_color='#0F172A'):
        for i in range(len(pts) - 1):
            x1, y1 = pts[i]
            x2, y2 = pts[i+1]
            if i == len(pts) - 2:
                ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                            arrowprops=dict(arrowstyle="-|>", color="#1E293B", lw=1.4, mutation_scale=12))
            else:
                ax.plot([x1, x2], [y1, y2], color="#1E293B", lw=1.4)
        if label:
            mid_pt = pts[len(pts)//2]
            ax.text(mid_pt[0] + 1.2, mid_pt[1], label, fontsize=7.2, color=lbl_color, fontweight='bold')

    # Start
    c_start = patches.Circle((16.6, 103), 1.8, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c_start)

    draw_act(16.6, 94, 26, 5.2, "Transaksi Presensi Berhasil\nTersimpan di Database")
    ortho_arrow([(16.6, 101.2), (16.6, 96.6)])

    draw_act(50.0, 94, 26, 5.2, "Panggil TelegramHelper\nFormat Pesan Notifikasi HTML")
    ortho_arrow([(29.6, 94), (37.0, 94)])

    draw_dec(50.0, 81, "Ekstensi cURL Tersedia?")
    ortho_arrow([(50.0, 91.4), (50.0, 83.5)])

    draw_act(83.3, 81, 26, 5.2, "Eksekusi cURL POST HTTPS\n(Timeout 8 Detik)")
    ortho_arrow([(53.8, 81), (70.3, 81)], label="[Ya]", lbl_color='#16A34A')

    draw_act(50.0, 68, 26, 5.2, "Fallback: PHP Stream Context\n(file_get_contents, 10s)", bg='#FEF3C7', border='#D97706')
    ortho_arrow([(50.0, 78.5), (50.0, 70.6)], label="[Tidak/Gagal]", lbl_color='#D97706')

    draw_dec(83.3, 56, "Response OK (200)?")
    ortho_arrow([(83.3, 78.4), (83.3, 58.5)])
    ortho_arrow([(63.0, 68), (83.3, 68), (83.3, 58.5)])

    draw_act(83.3, 41, 26, 5.0, "Kirim Pesan ke Saluran Chat\n(Grup Orang Tua / Guru)", bg='#DCFCE7', border='#16A34A')
    ortho_arrow([(83.3, 53.5), (83.3, 43.5)], label="[Ya]", lbl_color='#16A34A')

    draw_act(50.0, 41, 26, 5.0, "Catat Error Silent Log\n(Tanpa Bocor Token Rahasia)", bg='#FEE2E2', border='#DC2626')
    ortho_arrow([(79.5, 56), (50.0, 56), (50.0, 43.5)], label="[Gagal]", lbl_color='#DC2626')

    draw_act(16.6, 26, 26, 5.2, "Sistem Lanjut Operasional Normal\n(Presensi Tetap Sah Sukses)")
    ortho_arrow([(83.3, 38.5), (83.3, 26), (29.6, 26)])
    ortho_arrow([(50.0, 38.5), (50.0, 26), (29.6, 26)])

    c1 = patches.Circle((16.6, 12), 1.8, facecolor='#FFFFFF', edgecolor='#0F172A', lw=1.5)
    c2 = patches.Circle((16.6, 12), 1.1, facecolor='#0F172A', edgecolor='#0F172A')
    ax.add_patch(c1)
    ax.add_patch(c2)
    ortho_arrow([(16.6, 23.4), (16.6, 13.8)])

    plt.tight_layout()
    out_file = os.path.join(out_dir, "activity_diagram_telegram.png")
    if os.path.exists(out_file):
        os.remove(out_file)
    plt.savefig(out_file, dpi=300, bbox_inches='tight')
    plt.close()
    print("Activity Diagram Telegram generated successfully.")

# ==============================================================================
# 5. DIAGRAM RELASI ENTITAS DATA UML / ERD (PRIMARY KEY & FOREIGN KEY EXPLICIT)
# ==============================================================================
def draw_perfect_class_diagram_erd():
    fig, ax = plt.subplots(figsize=(18, 14), dpi=300)
    ax.set_xlim(0, 180)
    ax.set_ylim(0, 140)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # Title Banner
    banner = patches.FancyBboxPatch((8, 130), 164, 8, boxstyle="round,pad=0.5,rounding_size=1.0",
                                     facecolor='#1E3A8A', edgecolor='#1E3A8A')
    ax.add_patch(banner)
    ax.text(90, 134, "DIAGRAM ENTITAS DATA BASIS DATA (PHYSICAL DATA MODEL / UML DATA MODEL)",
            ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=12)
    ax.text(90, 131.5, "Database: db_absensi_smk  |  Menampilkan Seluruh Primary Key [PK], Foreign Key [FK], dan Kardinalitas Relasi",
            ha='center', va='center', color='#93C5FD', fontsize=8.5, fontstyle='italic')

    # Draw Entity Class Box Function
    entities = {}
    def draw_entity_box(key, cx, cy, w, h, table_name, pk_list, fk_list, attr_list):
        x = cx - w/2
        y = cy - h/2
        
        # Outer Card
        card = patches.FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.3,rounding_size=0.6",
                                      facecolor='#FFFFFF', edgecolor='#1E3A8A', lw=1.6)
        ax.add_patch(card)
        
        # Header Box
        hdr_h = 5.2
        hdr = patches.Rectangle((x, y + h - hdr_h), w, hdr_h, facecolor='#1E3A8A', edgecolor='#1E3A8A')
        ax.add_patch(hdr)
        
        ax.text(cx, y + h - 1.8, "«entity»", ha='center', va='center', color='#93C5FD', fontsize=7, fontstyle='italic')
        ax.text(cx, y + h - 3.8, table_name, ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        
        # Attributes
        curr_y = y + h - hdr_h - 2.0
        
        # 1. Primary Keys (Gold/Dark Red)
        for pk in pk_list:
            ax.text(x + 1.2, curr_y, "[PK]", ha='left', va='center', color='#B45309', fontweight='bold', fontsize=7.2, fontfamily='monospace')
            ax.text(x + 5.5, curr_y, pk, ha='left', va='center', color='#0F172A', fontweight='bold', fontsize=7.2, fontfamily='monospace')
            curr_y -= 2.2
            
        # 2. Foreign Keys (Royal Blue)
        for fk in fk_list:
            ax.text(x + 1.2, curr_y, "[FK]", ha='left', va='center', color='#2563EB', fontweight='bold', fontsize=7.2, fontfamily='monospace')
            ax.text(x + 5.5, curr_y, fk, ha='left', va='center', color='#1E293B', fontweight='semibold', fontsize=7.2, fontfamily='monospace')
            curr_y -= 2.2
            
        # Line Divider
        if pk_list or fk_list:
            ax.plot([x, x + w], [curr_y, curr_y], color='#CBD5E1', lw=0.8)
            curr_y -= 1.8
            
        # 3. Regular Attributes (Slate)
        for attr in attr_list:
            ax.text(x + 1.2, curr_y, attr, ha='left', va='center', color='#475569', fontsize=6.8, fontfamily='monospace')
            curr_y -= 2.0

        entities[key] = (cx, cy, w, h)
        return (cx, cy)

    # ── ROW 1 (Top): users (Center), guru (Left-Mid), siswa (Right-Mid), ai_configurations (Far Right) ──
    # 1. users
    draw_entity_box("users", 80, 112, 32, 22, "users",
                    ["id : INT(11) AUTO_INC"],
                    [],
                    ["username : VARCHAR(50) [UQ]",
                     "password : VARCHAR(255)",
                     "nama : VARCHAR(100)",
                     "role : ENUM(5)",
                     "status : ENUM('active','inactive')",
                     "created_at : TIMESTAMP"])

    # 2. guru
    draw_entity_box("guru", 30, 112, 34, 24, "guru",
                    ["id : INT(11) AUTO_INC"],
                    ["user_id : INT(11) -> users"],
                    ["nip : VARCHAR(20) [UQ]",
                     "nama : VARCHAR(100)",
                     "mata_pelajaran : VARCHAR(100)",
                     "barcode : VARCHAR(100) [UQ]",
                     "no_hp : VARCHAR(20)",
                     "status : ENUM('active','inactive')"])

    # 3. siswa
    draw_entity_box("siswa", 130, 112, 34, 25, "siswa",
                    ["id : INT(11) AUTO_INC"],
                    ["user_id : INT(11) -> users",
                     "kelas_id : INT(11) -> kelas"],
                    ["nis : VARCHAR(20) [UQ]",
                     "nisn : VARCHAR(20) [UQ]",
                     "nama : VARCHAR(100)",
                     "jenis_kelamin : ENUM('L','P')",
                     "rfid_uid : VARCHAR(50) [UQ]",
                     "barcode : VARCHAR(100) [UQ]",
                     "status : ENUM('active','inactive')"])

    # 4. ai_configurations (Standalone)
    draw_entity_box("ai_configurations", 165, 112, 26, 20, "ai_configurations",
                    ["id : INT(11) AUTO_INC"],
                    [],
                    ["label : VARCHAR(100)",
                     "provider : VARCHAR(50)",
                     "api_key : TEXT",
                     "model : VARCHAR(100)",
                     "is_active : TINYINT(1)",
                     "last_status : VARCHAR(50)"])

    # ── ROW 2 (Middle): kelas, mata_pelajaran, jadwal_absensi ──
    # 5. kelas
    draw_entity_box("kelas", 80, 78, 30, 19, "kelas",
                    ["id : INT(11) AUTO_INC"],
                    ["wali_kelas_id : INT(11) -> guru"],
                    ["nama_kelas : VARCHAR(50)",
                     "jurusan : VARCHAR(100)",
                     "tingkat : VARCHAR(10)"])

    # 6. mata_pelajaran
    draw_entity_box("mata_pelajaran", 25, 78, 30, 18, "mata_pelajaran",
                    ["id : INT(11) AUTO_INC"],
                    [],
                    ["kode_mapel : VARCHAR(20) [UQ]",
                     "nama_mapel : VARCHAR(100)",
                     "jam_per_minggu : INT(11)",
                     "status : ENUM('active','inactive')"])

    # 7. jadwal_absensi
    draw_entity_box("jadwal_absensi", 140, 78, 32, 20, "jadwal_absensi",
                    ["id : INT(11) AUTO_INC"],
                    ["kelas_id : INT(11) -> kelas [NULL]"],
                    ["nama_jadwal : VARCHAR(100)",
                     "tipe_pengguna : ENUM('siswa','guru')",
                     "hari : VARCHAR(50)",
                     "jam_mulai / jam_masuk : TIME",
                     "is_active : TINYINT(1)"])

    # ── ROW 3 (Bottom): absensi_guru, jadwal_mengajar, catatan_siswa, absensi_siswa ──
    # 8. absensi_guru
    draw_entity_box("absensi_guru", 25, 36, 30, 20, "absensi_guru",
                    ["id : INT(11) AUTO_INC"],
                    ["guru_id : INT(11) -> guru"],
                    ["tanggal : DATE [UQ_PAIR]",
                     "jam_scan : TIME",
                     "status : ENUM(5)",
                     "keterangan : TEXT"])

    # 9. jadwal_mengajar
    draw_entity_box("jadwal_mengajar", 65, 36, 32, 22, "jadwal_mengajar",
                    ["id : INT(11) AUTO_INC"],
                    ["guru_id : INT(11) -> guru",
                     "kelas_id : INT(11) -> kelas",
                     "mapel_id : INT(11) -> mata_pelajaran"],
                    ["hari : ENUM(6)",
                     "jam_mulai / jam_selesai : TIME",
                     "ruangan : VARCHAR(50)",
                     "status : ENUM('active','inactive')"])

    # 10. catatan_siswa
    draw_entity_box("catatan_siswa", 108, 36, 32, 21, "catatan_siswa",
                    ["id : INT(11) AUTO_INC"],
                    ["siswa_id : INT(11) -> siswa",
                     "wali_kelas_id : INT(11) -> guru"],
                    ["kategori : ENUM(7)",
                     "tanggal : DATE",
                     "catatan : TEXT"])

    # 11. absensi_siswa
    draw_entity_box("absensi_siswa", 150, 36, 30, 21, "absensi_siswa",
                    ["id : INT(11) AUTO_INC"],
                    ["siswa_id : INT(11) -> siswa"],
                    ["tanggal : DATE [UQ_PAIR]",
                     "jam_masuk / pulang : TIME",
                     "status : ENUM(5)",
                     "keterangan : TEXT"])

    # ── DRAW RELATIONSHIP CONNECTORS (FK -> PK) WITH CARDINALITY ──
    def draw_relation(from_ent, to_ent, card_from, card_to, label="", side_from='right', side_to='left', color='#0284C7'):
        cx1, cy1, w1, h1 = entities[from_ent]
        cx2, cy2, w2, h2 = entities[to_ent]
        
        # Simple point calculation
        if side_from == 'right': p1 = (cx1 + w1/2, cy1)
        elif side_from == 'left': p1 = (cx1 - w1/2, cy1)
        elif side_from == 'top': p1 = (cx1, cy1 + h1/2)
        else: p1 = (cx1, cy1 - h1/2)

        if side_to == 'right': p2 = (cx2 + w2/2, cy2)
        elif side_to == 'left': p2 = (cx2 - w2/2, cy2)
        elif side_to == 'top': p2 = (cx2, cy2 + h2/2)
        else: p2 = (cx2, cy2 - h2/2)

        ax.annotate("", xy=p2, xytext=p1,
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.5, ls="-", mutation_scale=10),
                    zorder=2)
        
        # Cardinality text
        ax.text(p1[0], p1[1] + 1.2, card_from, fontsize=7.2, color='#0F172A', fontweight='bold')
        ax.text(p2[0], p2[1] + 1.2, card_to, fontsize=7.2, color='#0F172A', fontweight='bold')
        if label:
            mx, my = (p1[0] + p2[0])/2, (p1[1] + p2[1])/2
            ax.text(mx, my + 1.2, label, fontsize=6.8, color=color, fontstyle='italic', ha='center',
                    bbox=dict(boxstyle="round,pad=0.15", facecolor='#FFFFFF', edgecolor=color, lw=0.6))

    # 1. users (1) <--- (1) guru
    draw_relation("guru", "users", "1", "1", label="fk_guru_user", side_from='right', side_to='left')

    # 2. users (1) <--- (1) siswa
    draw_relation("siswa", "users", "1", "1", label="fk_siswa_user", side_from='left', side_to='right')

    # 3. guru (1) <--- (0..1) kelas (Wali Kelas)
    draw_relation("kelas", "guru", "0..1", "1", label="fk_kelas_wali", side_from='left', side_to='bottom')

    # 4. kelas (1) <--- (0..*) siswa
    draw_relation("siswa", "kelas", "0..*", "1", label="fk_siswa_kelas", side_from='bottom', side_to='right')

    # 5. guru (1) <--- (0..*) absensi_guru
    draw_relation("absensi_guru", "guru", "0..*", "1", label="fk_absensi_guru", side_from='top', side_to='bottom')

    # 6. siswa (1) <--- (0..*) absensi_siswa
    draw_relation("absensi_siswa", "siswa", "0..*", "1", label="fk_absensi_siswa", side_from='top', side_to='bottom')

    # 7. siswa (1) <--- (0..*) catatan_siswa
    draw_relation("catatan_siswa", "siswa", "0..*", "1", label="fk_catatan_siswa", side_from='top', side_to='bottom')

    # 8. kelas (1) <--- (0..*) jadwal_mengajar
    draw_relation("jadwal_mengajar", "kelas", "0..*", "1", label="fk_jm_kelas", side_from='top', side_to='bottom')

    # 9. mata_pelajaran (1) <--- (0..*) jadwal_mengajar
    draw_relation("jadwal_mengajar", "mata_pelajaran", "0..*", "1", label="fk_jm_mapel", side_from='left', side_to='right')

    # 10. kelas (1) <--- (0..*) jadwal_absensi
    draw_relation("jadwal_absensi", "kelas", "0..*", "1", label="fk_jadwal_kelas", side_from='left', side_to='right')

    plt.tight_layout()
    out_file = os.path.join(out_dir, "class_diagram.png")
    if os.path.exists(out_file):
        os.remove(out_file)
    plt.savefig(out_file, dpi=300, bbox_inches='tight')
    plt.close()
    print("Class Diagram / ERD Physical Data Model generated successfully at:", out_file)

if __name__ == "__main__":
    draw_perfect_use_case()
    draw_perfect_activity_presensi()
    draw_perfect_activity_ai()
    draw_perfect_activity_telegram()
    draw_perfect_class_diagram_erd()

# ==============================================================================
# 6. CLASS DIAGRAM ARSITEKTUR MVC & SOLID PATTERNS
# ==============================================================================
def draw_perfect_class_diagram_mvc():
    fig, ax = plt.subplots(figsize=(15, 11), dpi=300)
    ax.set_xlim(0, 150)
    ax.set_ylim(0, 115)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # Title Banner
    banner = patches.FancyBboxPatch((8, 105), 134, 7.5, boxstyle="round,pad=0.5,rounding_size=1.0",
                                     facecolor='#1E3A8A', edgecolor='#1E3A8A')
    ax.add_patch(banner)
    ax.text(75, 109, "CLASS DIAGRAM ARSITEKTUR BERORIENTASI OBJEK (OOP MVC & SOLID PATTERNS)",
            ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=11)
    ax.text(75, 106.8, "Penerapan Prinsip SOLID, Desain Pola Factory, Provider Adapter, dan Helper Enkapsulasi",
            ha='center', va='center', color='#93C5FD', fontsize=8, fontstyle='italic')

    def draw_class_box(cx, cy, w, h, name, attrs, methods, is_interface=False, header_bg='#1E3A8A'):
        x = cx - w/2
        y = cy - h/2
        rect = patches.FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.3,rounding_size=0.6",
                                      facecolor='#FFFFFF', edgecolor=header_bg, lw=1.5)
        ax.add_patch(rect)
        hdr_h = 5.2
        hdr_rect = patches.Rectangle((x, y + h - hdr_h), w, hdr_h, facecolor=header_bg, edgecolor=header_bg)
        ax.add_patch(hdr_rect)
        
        prefix = "«interface»\n" if is_interface else ""
        ax.text(cx, y + h - 2.6, f"{prefix}{name}", ha='center', va='center', color='#FFFFFF', fontweight='bold', fontsize=8.5)
        
        curr_y = y + h - hdr_h - 2.0
        for a in attrs:
            ax.text(x + 1.2, curr_y, a, ha='left', va='center', color='#334155', fontsize=7.2, fontfamily='monospace')
            curr_y -= 2.4
            
        if attrs and methods:
            ax.plot([x, x + w], [curr_y, curr_y], color='#CBD5E1', lw=0.8)
            curr_y -= 1.8
            
        for m in methods:
            ax.text(x + 1.2, curr_y, m, ha='left', va='center', color='#0F172A', fontsize=7.2, fontfamily='monospace', fontweight='semibold')
            curr_y -= 2.4
        return (cx, cy)

    # Core Base
    draw_class_box(22, 90, 28, 18, "Controller", 
                   ["# db: Database"], 
                   ["+ view(view, data)", "+ model(model)"])

    draw_class_box(22, 54, 30, 24, "Absensi_siswa",
                   ["- model: Absensi_model", "- jadwal: Jadwal_model"],
                   ["+ index()", "+ scan()", "+ validasiRadius()", "+ catatPresensi()", "+ getRiwayat()"])

    draw_class_box(68, 90, 32, 22, "Database",
                   ["- dbHandler: PDO", "- stmt: PDOStatement"],
                   ["+ query(sql)", "+ bind(param, val)", "+ execute(): bool", "+ resultSet(): array", "+ single(): array"])

    draw_class_box(68, 52, 34, 28, "Absensi_model",
                   ["- table: string", "- db: Database"],
                   ["+ evaluasiWaktuJadwal()", "+ catatAbsensiSiswaBarcode()", "+ catatAbsensiGuruBarcode()", "+ kirimNotifikasiTelegramSafe()"])

    draw_class_box(120, 90, 36, 20, "AiProviderInterface",
                   [],
                   ["+ sendMessage(p, c): array", "+ testConnection(): array"],
                   is_interface=True, header_bg='#047857')

    draw_class_box(120, 58, 34, 18, "AiProviderFactory",
                   [],
                   ["+ create(config): AiProvider"],
                   header_bg='#047857')

    draw_class_box(102, 22, 30, 22, "GeminiAdapter",
                   ["- apiKey: string", "- model: string", "- baseUrl: string"],
                   ["+ sendMessage()", "+ testConnection()"],
                   header_bg='#2563EB')

    draw_class_box(136, 22, 30, 22, "OpenAiAdapter",
                   ["- apiKey: string", "- model: string", "- baseUrl: string"],
                   ["+ sendMessage()", "+ testConnection()"],
                   header_bg='#2563EB')

    draw_class_box(22, 18, 30, 22, "TelegramHelper",
                   ["- token: string", "- chatId: string"],
                   ["+ isEnabled(): bool", "+ kirimPesan(text): array", "+ notifikasiAbsensiSiswa()", "+ notifikasiAbsensiGuru()"],
                   header_bg='#D97706')

    draw_class_box(62, 16, 32, 20, "LokasiHelper",
                   ["- latSekolah: float", "- lngSekolah: float"],
                   ["+ hitungJarakMeter()", "+ validasiJarak(): array"],
                   header_bg='#D97706')

    # Connections
    def connect_arrow(x1, y1, x2, y2, ls='-', color='#475569'):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.4, ls=ls, mutation_scale=10))

    connect_arrow(22, 66, 22, 81)
    connect_arrow(37, 54, 51, 54)
    connect_arrow(68, 66, 68, 79)
    connect_arrow(51, 40, 37, 26, ls='--')
    connect_arrow(28, 42, 48, 22, ls='--')
    connect_arrow(120, 67, 120, 80, ls='--')
    connect_arrow(102, 33, 114, 50, ls='--')
    connect_arrow(136, 33, 126, 50, ls='--')

    plt.tight_layout()
    out_file = os.path.join(out_dir, "class_diagram_mvc.png")
    if os.path.exists(out_file):
        os.remove(out_file)
    plt.savefig(out_file, dpi=300, bbox_inches='tight')
    plt.close()
    print("Class Diagram MVC generated successfully at:", out_file)

if __name__ == "__main__":
    draw_perfect_class_diagram_mvc()
