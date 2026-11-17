import matplotlib.pyplot as plt
import matplotlib.patches as patches
import os

def generate_use_case():
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
    ax.text(sb_x + 6, sb_y + sb_h - 5, 'Sistem Informasi Presensi & Monitoring Absensi SMK NU Wargabinangun',
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

if __name__ == '__main__':
    generate_use_case()
