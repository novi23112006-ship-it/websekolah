import matplotlib.pyplot as plt
import matplotlib.patches as patches
import os

def generate_sequence_ai():
    fig, ax = plt.subplots(figsize=(20, 15), dpi=300)
    ax.set_xlim(0, 190)
    ax.set_ylim(0, 150)
    ax.axis('off')
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')

    # Title & Subtitle Banner
    title_box = patches.Rectangle((8, 138), 174, 9, facecolor='#1E3A8A', edgecolor='#1E3A8A')
    ax.add_patch(title_box)
    ax.text(95, 143.5, "SEQUENCE DIAGRAM: ALUR INTERAKSI & KOMUNIKASI ASISTEN VIRTUAL AI (CHATBOT) - SMK NU WARGABINANGUN",
            ha='center', va='center', fontsize=12.5, fontweight='bold', color='#FFFFFF')
    ax.text(95, 140.2, "Arsitektur Multi-Provider (Gemini & OpenAI) dengan Injeksi Konteks Database Sekolah Real-Time",
            ha='center', va='center', fontsize=8.5, fontstyle='italic', color='#93C5FD')

    # Lifeline Definitions: (id, name, tech_label, x_pos)
    lifelines = [
        ('user', 'Pengguna Sistem', '«actor»\n(5 Role Aktor)', 20),
        ('front', 'Chatbot UI Widget', '«boundary»\nviews/layouts/footer.php', 46),
        ('ctrl', 'Chatbot Controller', '«controller»\napp/controllers/Chatbot.php', 74),
        ('auth', 'Session & Auth', '«utility»\n$_SESSION (Role/User)', 101),
        ('db', 'Database Context', '«model/core»\napp/core/Database.php', 128),
        ('adapter', 'AI Provider Factory', '«service»\nAiProviderFactory/Adapter', 155),
        ('cloud', 'AI Cloud Provider', '«external API»\nGoogle Gemini / OpenAI', 180)
    ]

    header_y = 130
    bottom_y = 10

    # Draw Lifeline Headers & Vertical Dashed Lines
    for lid, name, tech, x in lifelines:
        # Header Box
        if lid == 'user':
            # Actor stick figure
            head = patches.Circle((x, header_y + 4.2), 1.8, edgecolor='#0F172A', facecolor='#EFF6FF', lw=1.6)
            ax.add_patch(head)
            ax.plot([x, x], [y_body := header_y + 2.4, header_y - 2.5], color='#0F172A', lw=1.8)
            ax.plot([x - 3.2, x + 3.2], [header_y + 0.6, header_y + 0.6], color='#0F172A', lw=1.8)
            ax.plot([x, x - 2.5], [header_y - 2.5, header_y - 6.0], color='#0F172A', lw=1.8)
            ax.plot([x, x + 2.5], [header_y - 2.5, header_y - 6.0], color='#0F172A', lw=1.8)
            ax.text(x, header_y - 7.5, name, ha='center', va='top', fontsize=9, fontweight='bold', color='#0F172A')
            ax.text(x, header_y - 10.2, tech, ha='center', va='top', fontsize=7.2, fontstyle='italic', color='#475569')
        else:
            hb = patches.Rectangle((x - 11, header_y - 6), 22, 10, facecolor='#F1F5F9', edgecolor='#1E3A8A', lw=1.5)
            ax.add_patch(hb)
            ax.text(x, header_y + 1.2, name, ha='center', va='center', fontsize=8.2, fontweight='bold', color='#0F172A')
            ax.text(x, header_y - 2.8, tech, ha='center', va='center', fontsize=6.8, fontstyle='italic', color='#2563EB')

        # Lifeline vertical dashed line
        ax.plot([x, x], [header_y - 12, bottom_y], color='#94A3B8', lw=1.2, ls='--', zorder=1)

    # Activation Boxes (Thin white/blue rectangles on lifelines)
    def draw_activation(x, y_start, y_end):
        h = y_start - y_end
        rect = patches.Rectangle((x - 1.2, y_end), 2.4, h, facecolor='#DBEAFE', edgecolor='#1D4ED8', lw=1.0, zorder=2)
        ax.add_patch(rect)

    draw_activation(20, 115, 15)   # User
    draw_activation(46, 114, 18)   # Frontend
    draw_activation(74, 111, 23)   # Controller
    draw_activation(101, 106, 96)  # Auth
    draw_activation(128, 93, 76)   # Database
    draw_activation(155, 72, 38)   # Adapter
    draw_activation(180, 64, 46)   # Cloud API

    # Message Helper Functions
    def sync_msg(x1, x2, y, step_num, text, is_return=False, color='#0F172A'):
        ls = '--' if is_return else '-'
        ax.annotate('', xy=(x2, y), xytext=(x1, y),
                    arrowprops=dict(arrowstyle='->', color=color, lw=1.3, ls=ls, mutation_scale=10), zorder=3)
        mid_x = (x1 + x2) / 2
        lbl = f"{step_num}. {text}" if step_num else text
        ax.text(mid_x, y + 1.3, lbl, ha='center', va='bottom', fontsize=7.6, fontweight='bold', color=color,
                bbox=dict(boxstyle='round,pad=0.15', facecolor='#FFFFFF', edgecolor='#CBD5E1', lw=0.6))

    def self_call(x, y_start, y_end, step_num, text):
        ax.plot([x, x + 6, x + 6, x], [y_start, y_start, y_end, y_end], color='#475569', lw=1.2, zorder=3)
        ax.annotate('', xy=(x, y_end), xytext=(x + 2, y_end),
                    arrowprops=dict(arrowstyle='->', color='#475569', lw=1.2, mutation_scale=8))
        lbl = f"{step_num}. {text}" if step_num else text
        ax.text(x + 7, (y_start + y_end)/2, lbl, ha='left', va='center', fontsize=7.2, fontweight='bold', color='#475569')

    # ══════════════════════════════════════════════════════════════════════════════
    # SEQUENTIAL INTERACTION FLOW (8 MAIN STEPS)
    # ══════════════════════════════════════════════════════════════════════════════

    # Step 1: User types message in chatbot UI
    sync_msg(20, 46, 114, "1", "Input pertanyaan teks & klik 'Kirim' pada widget chat", color='#1E3A8A')

    # Step 2: Frontend dispatches HTTP POST AJAX to backend
    sync_msg(46, 74, 110, "2", "POST /websekolah3/public/chatbot (JSON: { message })", color='#1E3A8A')

    # Step 3: Controller checks session & user role
    sync_msg(74, 101, 104, "3a", "Cek session aktif & role (admin, kepsek, wali, guru, siswa)", color='#0284C7')
    sync_msg(101, 74, 98, "3b", "Return $_SESSION['role', 'user_id', 'nama']", is_return=True, color='#0284C7')

    # Step 4: Controller fetches realtime school data context matching user role
    sync_msg(74, 128, 91, "4a", "Query data presensi, KBM & rekap kehadiran sesuai hak akses role", color='#059669')
    self_call(128, 87, 82, "4b", "SELECT data guru/siswa & hitung persentase kehadiran hari ini")
    sync_msg(128, 74, 78, "4c", "Return formatted context array data sekolah aktual", is_return=True, color='#059669')

    # Self-processing: Controller constructs System Prompt Injection & safety checks
    self_call(74, 76, 71, "4d", "buildSystemPrompt(role, context) & keyword guardrails filter")

    # Step 5: Controller invokes AI Factory & active Adapter
    sync_msg(74, 155, 68, "5a", "panggilAiAPI(system_context, message) via AiProviderFactory", color='#7C3AED')
    sync_msg(155, 180, 62, "5b", "HTTP POST generateContent (Prompt + Konteks Sekolah)", color='#7C3AED')

    # Step 6: AI Cloud Provider executes inference and returns answer
    self_call(180, 58, 53, "6a", "LLM Inference Process (Gemini Flash / GPT-4o-mini)")
    sync_msg(180, 155, 48, "6b", "HTTP 200 OK Response (JSON: candidates / choices)", is_return=True, color='#7C3AED')
    sync_msg(155, 74, 43, "6c", "Return generated response text string", is_return=True, color='#7C3AED')

    # Step 7: Controller sends formatted JSON back to Frontend UI
    sync_msg(74, 46, 30, "7", "HTTP 200 OK (JSON: { success: true, message: jawaban_ai })", is_return=True, color='#1E3A8A')

    # Step 8: Frontend widget renders markdown/HTML text into chat bubble
    sync_msg(46, 20, 20, "8", "Render jawaban pada bubble chat UI widget & putar audio notif", is_return=True, color='#1E3A8A')

    # Fallback Note Box
    note_box = patches.Rectangle((8, 11), 32, 6.5, facecolor='#FEF9C3', edgecolor='#CA8A04', lw=0.9)
    ax.add_patch(note_box)
    ax.text(24, 14.25, "[*] Catatan Dual-Fallback:\nJika Cloud AI timeout/error, Controller memanggil\nrule-based keyword response lokal secara instan.",
            ha='center', va='center', fontsize=6.5, color='#854D0E')

    plt.tight_layout()
    
    for base in [r"C:\xampp\htdocs\websekolah3\laporan word", r"C:\xampp1\htdocs\websekolah3\laporan word"]:
        if os.path.exists(base):
            out_file = os.path.join(base, "sequence_diagram_ai.png")
            plt.savefig(out_file, dpi=300, bbox_inches='tight')
            print(f"Generated successfully: {out_file}")
            
    plt.close()

if __name__ == '__main__':
    generate_sequence_ai()
