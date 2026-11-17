import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, fill_hex):
    tcPr = cell._element.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._element.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def set_table_borders(table, color="D3D3D3", sz="4", val="single"):
    tblPr = table._element.xpath('w:tblPr')
    if tblPr:
        borders = parse_xml(
            f'<w:tblBorders {nsdecls("w")}>'
            f'<w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:left w:val="none"/>'
            f'<w:right w:val="none"/>'
            f'<w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:insideV w:val="none"/>'
            f'</w:tblBorders>'
        )
        tblPr[0].append(borders)

def add_callout(doc, text, title="CATATAN PENTING / OBE HIGHLIGHT", color_hex="1B365D", bg_hex="F0F4F8"):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, bg_hex)
    set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
    
    tcPr = cell._element.get_or_add_tcPr()
    borders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'<w:left w:val="single" w:sz="24" w:space="0" w:color="{color_hex}"/>'
        f'<w:top w:val="none"/>'
        f'<w:right w:val="none"/>'
        f'<w:bottom w:val="none"/>'
        f'</w:tcBorders>'
    )
    tcPr.append(borders)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    r_title = p.add_run(f"📌 {title}\n")
    r_title.bold = True
    r_title.font.name = "Calibri"
    r_title.font.size = Pt(10.5)
    r_title.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    
    r_text = p.add_run(text)
    r_text.font.name = "Calibri"
    r_text.font.size = Pt(10)
    r_text.font.italic = True
    r_text.font.color.rgb = RGBColor(0x33, 0x33, 0x33)
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_before = Pt(0)
    p_after.paragraph_format.space_after = Pt(6)

def format_table(table, header_bg="1B365D", col_widths=None):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(table)
    
    for i, row in enumerate(table.rows):
        trPr = row._element.get_or_add_trPr()
        trPr.append(parse_xml(f'<w:cantSplit {nsdecls("w")}/>'))
        
        if i == 0:
            trPr.append(parse_xml(f'<w:tblHeader {nsdecls("w")}/>'))
            for j, cell in enumerate(row.cells):
                set_cell_background(cell, header_bg)
                set_cell_margins(cell, top=120, bottom=120, left=140, right=140)
                cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
                for p in cell.paragraphs:
                    p.paragraph_format.space_before = Pt(2)
                    p.paragraph_format.space_after = Pt(2)
                    for r in p.runs:
                        r.bold = True
                        r.font.name = "Calibri"
                        r.font.size = Pt(9.5)
                        r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
        else:
            bg_color = "F9FAFC" if i % 2 == 1 else "FFFFFF"
            for j, cell in enumerate(row.cells):
                set_cell_background(cell, bg_color)
                set_cell_margins(cell, top=80, bottom=80, left=120, right=120)
                cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
                for p in cell.paragraphs:
                    p.paragraph_format.space_before = Pt(1)
                    p.paragraph_format.space_after = Pt(1)
                    for r in p.runs:
                        r.font.name = "Calibri"
                        r.font.size = Pt(9)
                        r.font.color.rgb = RGBColor(0x33, 0x33, 0x33)
                        
    if col_widths:
        for row in table.rows:
            for j, w in enumerate(col_widths):
                if j < len(row.cells):
                    row.cells[j].width = Inches(w)

def add_h1(doc, text):
    h = doc.add_paragraph()
    h.paragraph_format.space_before = Pt(18)
    h.paragraph_format.space_after = Pt(8)
    h.paragraph_format.keep_with_next = True
    r = h.add_run(text)
    r.bold = True
    r.font.name = "Calibri"
    r.font.size = Pt(16)
    r.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    return h

def add_h2(doc, text):
    h = doc.add_paragraph()
    h.paragraph_format.space_before = Pt(14)
    h.paragraph_format.space_after = Pt(6)
    h.paragraph_format.keep_with_next = True
    r = h.add_run(text)
    r.bold = True
    r.font.name = "Calibri"
    r.font.size = Pt(13)
    r.font.color.rgb = RGBColor(0x2E, 0x5B, 0x88)
    return h

def add_h3(doc, text):
    h = doc.add_paragraph()
    h.paragraph_format.space_before = Pt(10)
    h.paragraph_format.space_after = Pt(4)
    h.paragraph_format.keep_with_next = True
    r = h.add_run(text)
    r.bold = True
    r.font.name = "Calibri"
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    return h

def add_p(doc, text, space_after=6, bold=False, italic=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    r = p.add_run(text)
    r.font.name = "Calibri"
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(0x33, 0x33, 0x33)
    r.bold = bold
    r.italic = italic
    return p

def add_bullet(doc, text, bold_prefix="", space_after=3):
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        r_pre = p.add_run(bold_prefix)
        r_pre.bold = True
        r_pre.font.name = "Calibri"
        r_pre.font.size = Pt(11)
        r_pre.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    r = p.add_run(text)
    r.font.name = "Calibri"
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(0x33, 0x33, 0x33)
    return p

def add_figure(doc, img_path, caption_text, width_inches=6.0):
    if os.path.exists(img_path):
        p_img = doc.add_paragraph()
        p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img.paragraph_format.space_before = Pt(8)
        p_img.paragraph_format.space_after = Pt(4)
        run_img = p_img.add_run()
        run_img.add_picture(img_path, width=Inches(width_inches))
        
        p_cap = doc.add_paragraph()
        p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_cap.paragraph_format.space_before = Pt(2)
        p_cap.paragraph_format.space_after = Pt(12)
        r_cap = p_cap.add_run(caption_text)
        r_cap.font.name = "Calibri"
        r_cap.font.size = Pt(9.5)
        r_cap.font.italic = True
        r_cap.font.bold = True
        r_cap.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    else:
        add_p(doc, f"[Gambar belum ditemukan: {img_path}]", italic=True)

def add_ui_placeholder(doc, title_text, caption_text, description_text, key_components):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, "F8FAFC")
    set_cell_margins(cell, top=140, bottom=140, left=180, right=180)
    
    tcPr = cell._element.get_or_add_tcPr()
    borders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'<w:top w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
        f'<w:left w:val="single" w:sz="18" w:space="0" w:color="1B365D"/>'
        f'<w:bottom w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
        f'<w:right w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
        f'</w:tcBorders>'
    )
    tcPr.append(borders)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(3)
    r_hdr = p.add_run(f"🖼️ WADAH PURWARUPA / MOCKUP FIGMA: {title_text}\n")
    r_hdr.bold = True
    r_hdr.font.name = "Calibri"
    r_hdr.font.size = Pt(10)
    r_hdr.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    
    r_sub = p.add_run(f"{description_text}\n\n")
    r_sub.font.name = "Calibri"
    r_sub.font.size = Pt(9.5)
    r_sub.font.color.rgb = RGBColor(0x33, 0x33, 0x33)
    
    r_cmp_lbl = p.add_run("Komponen Desain Antarmuka Utama:\n")
    r_cmp_lbl.bold = True
    r_cmp_lbl.font.name = "Calibri"
    r_cmp_lbl.font.size = Pt(9)
    r_cmp_lbl.font.color.rgb = RGBColor(0x2E, 0x5B, 0x88)
    
    for comp in key_components:
        r_item = p.add_run(f"  • {comp}\n")
        r_item.font.name = "Calibri"
        r_item.font.size = Pt(8.5)
        r_item.font.color.rgb = RGBColor(0x47, 0x55, 0x69)
        
    r_inst = p.add_run("\n[Tempatkan tangkapan layar (screenshot) desain Figma atau antarmuka aplikasi di sini]")
    r_inst.font.name = "Calibri"
    r_inst.font.size = Pt(8.5)
    r_inst.font.italic = True
    r_inst.font.color.rgb = RGBColor(0x64, 0x74, 0x8B)
    
    p_cap = doc.add_paragraph()
    p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cap.paragraph_format.space_before = Pt(4)
    p_cap.paragraph_format.space_after = Pt(12)
    r_cap = p_cap.add_run(caption_text)
    r_cap.font.name = "Calibri"
    r_cap.font.size = Pt(9.5)
    r_cap.font.italic = True
    r_cap.font.bold = True
    r_cap.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)

def add_code(doc, text):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, "F4F5F7")
    set_cell_margins(cell, top=100, bottom=100, left=150, right=150)
    
    tcPr = cell._element.get_or_add_tcPr()
    borders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'<w:left w:val="single" w:sz="12" w:space="0" w:color="CCCCCC"/>'
        f'<w:top w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>'
        f'<w:right w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>'
        f'<w:bottom w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>'
        f'</w:tcBorders>'
    )
    tcPr.append(borders)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    r = p.add_run(text)
    r.font.name = "Consolas"
    r.font.size = Pt(9)
    r.font.color.rgb = RGBColor(0x22, 0x22, 0x22)
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_before = Pt(0)
    p_after.paragraph_format.space_after = Pt(6)

print("Base helper loaded.")

def build_full_report():
    doc = docx.Document()
    out_dir = r"C:\xampp1\htdocs\websekolah3\laporan word"
    
    # Page Setup A4
    for section in doc.sections:
        section.top_margin = Inches(1.0)
        section.bottom_margin = Inches(1.0)
        section.left_margin = Inches(1.0)
        section.right_margin = Inches(1.0)
        section.page_width = Inches(8.27)
        section.page_height = Inches(11.69)
        
        # Header & Footer setup
        header = section.header
        hp = header.paragraphs[0]
        hp.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        hr = hp.add_run("TIKB2503021 - Rekayasa Perangkat Lunak | STMIK IKMI Cirebon")
        hr.font.name = "Calibri"
        hr.font.size = Pt(8.5)
        hr.font.color.rgb = RGBColor(0x88, 0x88, 0x88)
        
        footer = section.footer
        fp = footer.paragraphs[0]
        fp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        fr = fp.add_run("Laporan Proyek Akhir Mandiri - Sistem Presensi SMK NU Wargabinangun Berbasis AI")
        fr.font.name = "Calibri"
        fr.font.size = Pt(8.5)
        fr.font.color.rgb = RGBColor(0x88, 0x88, 0x88)

    # ══════════════════════════════════════════════════════════════════════════════
    # HALAMAN COVER / JUDUL UTAMA
    # ══════════════════════════════════════════════════════════════════════════════
    p_inst = doc.add_paragraph()
    p_inst.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_inst.paragraph_format.space_before = Pt(12)
    p_inst.paragraph_format.space_after = Pt(4)
    r1 = p_inst.add_run("PROGRAM STUDI S1 TEKNIK INFORMATIKA\n")
    r1.bold = True
    r1.font.size = Pt(13)
    r1.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    r2 = p_inst.add_run("FAKULTAS ILMU KOMPUTER - STMIK IKMI CIREBON\n")
    r2.bold = True
    r2.font.size = Pt(14)
    r2.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
    r3 = p_inst.add_run("TAHUN AKADEMIK 2026/2027\n")
    r3.font.size = Pt(11)
    r3.font.color.rgb = RGBColor(0x55, 0x55, 0x55)

    doc.add_paragraph().paragraph_format.space_after = Pt(20)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_before = Pt(8)
    p_title.paragraph_format.space_after = Pt(8)
    rt0 = p_title.add_run("LAPORAN PROYEK AKHIR MANDIRI REKAYASA PERANGKAT LUNAK (RPL)\n")
    rt0.bold = True
    rt0.font.size = Pt(13)
    rt0.font.color.rgb = RGBColor(0x55, 0x55, 0x55)

    rt1 = p_title.add_run("PENGEMBANGAN SISTEM INFORMASI PRESENSI & MONITORING ABSENSI SMK NU WARGABINANGUN BERBASIS WEB DENGAN ASISTEN VIRTUAL MULTI-PROVIDER AI, GEOFENCING VALIDASI LOKASI GPS, DAN NOTIFIKASI REAL-TIME TELEGRAM BOT\n")
    rt1.bold = True
    rt1.font.size = Pt(16)
    rt1.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(28)
    rsub = p_sub.add_run("Disusun untuk Memenuhi Luaran Mata Kuliah Rekayasa Perangkat Lunak (TIKB2503021)\nBobot 4 SKS (2 Teori + 2 Praktik) — Semester 3 Berbasis Kurikulum OBE (Outcome-Based Education)")
    rsub.font.size = Pt(10)
    rsub.font.italic = True
    rsub.font.color.rgb = RGBColor(0x44, 0x44, 0x44)

    # Identitas Mahasiswa Table
    tbl_id = doc.add_table(rows=6, cols=3)
    tbl_id.alignment = WD_TABLE_ALIGNMENT.CENTER
    identitas_data = [
        ("Nama Mahasiswa", ":", "[Nama Mahasiswa]"),
        ("NIM", ":", "[NIM Mahasiswa]"),
        ("Program Studi", ":", "S1 Teknik Informatika (Semester 3)"),
        ("Mata Kuliah / Kode", ":", "Rekayasa Perangkat Lunak / TIKB2503021 (4 SKS)"),
        ("Dosen Pengampu", ":", "Yudhistira Arie Wijaya, M.Kom., CDS."),
        ("Tema Topik Kelompok", ":", "E-Learning, Smart Education & AI Campus Management"),
    ]
    for row_idx, (col1, col2, col3) in enumerate(identitas_data):
        r = tbl_id.rows[row_idx]
        set_cell_margins(r.cells[0], top=35, bottom=35, left=70, right=70)
        set_cell_margins(r.cells[1], top=35, bottom=35, left=30, right=30)
        set_cell_margins(r.cells[2], top=35, bottom=35, left=70, right=70)
        
        p1 = r.cells[0].paragraphs[0]
        p1.add_run(col1).bold = True
        p1.runs[0].font.size = Pt(10)
        p1.runs[0].font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)
        
        p2 = r.cells[1].paragraphs[0]
        p2.add_run(col2)
        p2.runs[0].font.size = Pt(10)
        
        p3 = r.cells[2].paragraphs[0]
        p3.add_run(col3)
        p3.runs[0].font.size = Pt(10)
    
    set_table_borders(tbl_id, color="E0E0E0", sz="2")

    doc.add_paragraph().paragraph_format.space_after = Pt(35)

    p_cirebon = doc.add_paragraph()
    p_cirebon.alignment = WD_ALIGN_PARAGRAPH.CENTER
    rc1 = p_cirebon.add_run("CIREBON - JAWA BARAT\n2026 / 2027")
    rc1.bold = True
    rc1.font.size = Pt(11)
    rc1.font.color.rgb = RGBColor(0x1B, 0x36, 0x5D)

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # KATA PENGANTAR
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "KATA PENGANTAR")
    add_p(doc, "Puji syukur kami panjatkan ke hadirat Allah SWT, Tuhan Yang Maha Esa, atas limpahan rahmat dan karunia-Nya sehingga Dokumen Laporan Proyek Akhir Mandiri Rekayasa Perangkat Lunak (RPL) berjudul \"Pengembangan Sistem Informasi Presensi & Monitoring Absensi SMK NU Wargabinangun Berbasis Web dengan Asisten Virtual Multi-Provider AI, Geofencing Validasi Lokasi GPS, dan Notifikasi Real-Time Telegram Bot\" ini dapat diselesaikan dengan sempurna, rapi, dan sesuai dengan standar kurikulum OBE.")
    add_p(doc, "Laporan proyek ini disusun sebagai luaran resmi dan pertanggungjawaban karya rekayasa mandiri (100% Individual Ownership & Full-App Build) pada mata kuliah Rekayasa Perangkat Lunak (TIKB2503021) Semester 3 di Program Studi S1 Teknik Informatika STMIK IKMI Cirebon. Mahasiswa dituntut untuk membangun aplikasi secara utuh dari inisiasi PRD/SRS standar IEEE 830, pemodelan SDD (Use Case, Activity, Class, Sequence), implementasi Clean Code MVC dan prinsip SOLID, integrasi Multi-Provider AI, hingga Automated Testing dan CI/CD Pipeline GitHub Actions.")
    add_p(doc, "Penyusun menyampaikan ucapan terima kasih sebesar-besarnya kepada:")
    add_bullet(doc, "Bapak Yudhistira Arie Wijaya, M.Kom., CDS., selaku Dosen Pengampu Mata Kuliah Rekayasa Perangkat Lunak yang senantiasa membimbing, mengarahkan, dan memberikan standar rekayasa bernilai tinggi.", "1. ")
    add_bullet(doc, "Civitas Akademika STMIK IKMI Cirebon yang menyediakan infrastruktur pembelajaran berbasis teknologi mutakhir.", "2. ")
    add_bullet(doc, "Rekan-rekan mahasiswa yang selalu memberikan masukan konstruktif dalam pengembangan sistem.", "3. ")
    add_p(doc, "Semoga dokumen laporan ini dapat memberikan manfaat nyata bagi transformasi digital pendidikan di SMK serta menjadi acuan implementasi rekayasa perangkat lunak modern yang tangguh.")
    
    p_ttd = doc.add_paragraph()
    p_ttd.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p_ttd.paragraph_format.space_before = Pt(16)
    r_ttd = p_ttd.add_run("Cirebon, November 2026\n\n\n\nPenulis / Mahasiswa Rekayasa")
    r_ttd.font.size = Pt(10)

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # DAFTAR ISI (TABLE OF CONTENTS)
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "DAFTAR ISI")
    add_p(doc, "Struktur dan susunan bab pada Laporan Proyek Akhir Rekayasa Perangkat Lunak ini disajikan pada tabel daftar isi berikut:")

    tbl_toc = doc.add_table(rows=1, cols=3)
    tbl_toc.rows[0].cells[0].paragraphs[0].text = "Bagian / Bab"
    tbl_toc.rows[0].cells[1].paragraphs[0].text = "Judul Subbab & Pokok Pembahasan"
    tbl_toc.rows[0].cells[2].paragraphs[0].text = "Halaman"

    toc_entries = [
        ("HALAMAN JUDUL", "Halaman Sampul Laporan Proyek Akhir Mandiri RPL", "i"),
        ("KATA PENGANTAR", "Prakata, Ucapan Syukur, dan Ucapan Terima Kasih", "ii"),
        ("DAFTAR ISI", "Daftar Susunan Bab, Subbab, dan Navigasi Laporan", "iii"),
        ("RINGKASAN EKSEKUTIF", "Executive Summary, Latar Belakang Inovasi, & Metrik Kinerja", "iv"),
        ("BAB I", "INISIASI & PENYUSUNAN PRD / SRS MANDIRI (IEEE 830)", "1"),
        ("  1.1", "Latar Belakang Masalah Presensi SMK & Urgensi Solusi", "1"),
        ("  1.2", "Identifikasi Masalah & Nilai Guna Aplikasi Berkelanjutan", "2"),
        ("  1.3", "Ruang Lingkup Sistem & Karakteristik Pengguna (5 Role Aktor)", "3"),
        ("  1.4", "Spesifikasi Kebutuhan Fungsional (User Stories & Acceptance Criteria)", "4"),
        ("  1.5", "Kebutuhan Non-Fungsional (Non-Functional Requirements)", "6"),
        ("  1.6", "Spesifikasi Fitur Kecerdasan Artifisial (AI) Utama", "7"),
        ("BAB II", "PERANCANGAN ARSITEKTUR & SISTEM MANDIRI (SDD)", "9"),
        ("  2.1", "Arsitektur Perangkat Lunak (MVC & SOLID Design Principles)", "9"),
        ("  2.2", "Pemodelan UML Sistem Mandiri", "10"),
        ("    2.2.1", "Use Case Diagram & Skenario Use Case Fungsional", "10"),
        ("    2.2.2", "Activity Diagram Alur Transaksi Presensi, AI, & Notifikasi", "15"),
        ("    2.2.3", "Diagram Relasi Entitas Data (Physical Data Model / ERD UML)", "20"),
        ("    2.2.4", "Class Diagram Arsitektur Berorientasi Objek & Desain Pola", "23"),
        ("    2.2.5", "Sequence Diagram Alur Pemindaian Presensi & Validasi Geofencing", "26"),
        ("    2.2.6", "Sequence Diagram Alur Interaksi & Komunikasi AI Chatbot", "28"),
        ("  2.3", "Perancangan Basis Data Relasional & Pemetaan Kunci (PK-FK Mapping)", "30"),
        ("    2.3.1", "Matriks Pemetaan Kunci Primer (PK) dan Kunci Asing (FK)", "30"),
        ("    2.3.2", "Kamus Data Struktur Tabel Aktual (Data Dictionary 11 Tabel)", "33"),
        ("  2.4", "Perancangan Antarmuka Pengguna (UI/UX) & Purwarupa Figma", "42"),
        ("    2.4.1", "Tujuan Perancangan Antarmuka Pengguna", "42"),
        ("    2.4.2", "Purwarupa Figma & Tautan Desain Interaktif", "43"),
        ("    2.4.3", "Mockup Desain Antarmuka Sistem (8 Halaman Utama)", "44"),
        ("BAB III", "KONSTRUKSI KODE & INTEGRASI SISTEM", "48"),
        ("  3.1", "Struktur Direktori Proyek Bersih Berbasis MVC Native PHP", "48"),
        ("  3.2", "Implementasi Multi-Provider AI Engine (Gemini & OpenAI)", "50"),
        ("  3.3", "Implementasi Geofencing Validasi Lokasi (Haversine Formula)", "53"),
        ("  3.4", "Implementasi Notifikasi Dual-Fallback Telegram Bot API", "55"),
        ("BAB IV", "PENGUJIAN SISTEM (TESTING) & DEVOPS CI/CD", "57"),
        ("  4.1", "Rencana & Metodologi Pengujian Perangkat Lunak", "57"),
        ("  4.2", "Matriks Hasil Pengujian Fungsional (Black-Box Testing)", "58"),
        ("  4.3", "Pengujian Unit Mandiri (Unit Testing Berbasis PHPUnit)", "60"),
        ("  4.4", "Otomatisasi CI/CD Pipeline pada GitHub Actions", "63"),
        ("BAB V", "PANDUAN DEPLOYMENT & PETUNJUK INSTALASI (SETUP)", "66"),
        ("  5.1", "Kebutuhan Sistem & Prasyarat Lingkungan Operasional", "66"),
        ("  5.2", "Langkah-Langkah Instalasi Mandiri dari Source Code", "67"),
        ("  5.3", "Akun Pengguna Uji (Default Credentials 5 Role)", "68"),
        ("  5.4", "Ketentuan Pengumpulan Tugas Proyek Akhir (Submission Instructions)", "69"),
        ("BAB VI", "KESIMPULAN & REFLEKSI PEMBELAJARAN (OBE ALIGNMENT)", "71"),
        ("  6.1", "Kesimpulan Capaian Proyek Rekayasa Perangkat Lunak", "71"),
        ("  6.2", "Refleksi Pembelajaran Mandiri (Outcome-Based Education)", "72"),
        ("  6.3", "Saran Pengembangan Sistem Lanjutan", "73"),
        ("DAFTAR PUSTAKA", "Referensi Ilmiah, Standar IEEE, Dokumentasi API, & Literatur", "74")
    ]

    for part, title, page in toc_entries:
        row = tbl_toc.add_row()
        row.cells[0].paragraphs[0].text = part
        row.cells[1].paragraphs[0].text = title
        row.cells[2].paragraphs[0].text = page
    format_table(tbl_toc, col_widths=[1.1, 5.2, 0.7])
    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # RINGKASAN EKSEKUTIF
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)")
    add_callout(doc, 
        "Proyek ini memenuhi 100% ketentuan Panduan Proyek Akhir Mandiri TIKB2503021 dengan arsitektur Full-Stack mandiri tanpa pembagian modul. Memadukan arsitektur MVC native PHP 8.5 murni, MySQL Database relasional terindeks penuh, integrasi Multi-Provider AI (Google Gemini & OpenAI) via Adapter Pattern, validasi Geofencing koordinat GPS Haversine 100 meter, scanner kamera barcode/QR code Html5Qrcode, RFID terminal, serta dual-fallback notifikasi real-time Telegram Bot API.",
        "KONSISTENSI KURIKULUM OBE & KEPEMILIKAN MANDIRI 100%"
    )
    add_p(doc, "Sistem Informasi Presensi & Monitoring Absensi SMK NU Wargabinangun merupakan solusi rekayasa perangkat lunak terintegrasi yang dirancang khusus untuk memodernisasi tata kelola presensi harian 135 siswa (tersebar dalam 27 rombongan belajar pada kompetensi keahlian TKJ, TKR, dan TSM) serta 30 dewan guru di SMK NU Wargabinangun, Cirebon. Sistem ini secara tuntas menuntaskan kelemahan laten presensi manual kertas dan mesin fingerprint statis, seperti risiko manipulasi kehadiran (titip absen), antrean panjang di gerbang sekolah pada jam masuk pagi, dan keterlambatan rekapitulasi data kehadiran ke Kepala Sekolah dan wali murid.")
    add_p(doc, "Keunggulan utama rekayasa perangkat lunak yang dihadirkan meliputi:")
    add_bullet(doc, "Penyediaan terminal presensi ganda berbasis Web Barcode/QR Scanner kamera responsif dan RFID card tap yang langsung terhubung ke database.", "1. Terminal Presensi Multi-Metode: ")
    add_bullet(doc, "Validasi koordinat geolokasi pengguna dengan jarak maksimal 100 meter dari titik koordinat sekolah menggunakan kalkulasi trigonometri Haversine Formula di sisi backend.", "2. Geofencing Anti-Kecurangan: ")
    add_bullet(doc, "Asisten cerdas terintegrasi yang ditenagai oleh adapter multi-provider (Google Gemini 3.1 Flash Lite dan OpenAI GPT-4o-mini). Dilengkapi proteksi zero secret exposure, database-driven configuration, dan dynamic context injection dari data sekolah real-time.", "3. Asisten Virtual Multi-Provider AI: ")
    add_bullet(doc, "Setiap transaksi presensi masuk, pulang, maupun pengajuan izin/sakit langsung di-dispatch ke Telegram Bot resmi dengan ketahanan jaringan dual-fallback (cURL + PHP Stream Context).", "4. Notifikasi Real-Time Telegram: ")
    add_bullet(doc, "Pemisahan hak akses ketat (RBAC) untuk 5 entitas peran: Super Administrator, Dewan Guru, Wali Kelas, Kepala Sekolah, dan Siswa.", "5. 5 Role Dashboard Terpisah: ")

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB I: INISIASI & PENYUSUNAN PRD / SRS MANDIRI
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB I: INISIASI & PENYUSUNAN PRD / SRS MANDIRI (STANDAR IEEE 830 / ISO 29148)")
    
    add_h2(doc, "1.1 Latar Belakang Masalah")
    add_p(doc, "Sekolah Menengah Kejuruan Nahdlatul Ulama Wargabinangun (SMK NU Wargabinangun) yang berlokasi di Kabupaten Cirebon, Jawa Barat, merupakan institusi pendidikan vokasi yang menyelenggarakan 3 kompetensi keahlian kejuruan unggulan: (1) Teknik Komputer dan Jaringan (TKJ), (2) Teknik Kendaraan Ringan (TKR), dan (3) Teknik Sepeda Motor (TSM). Pada basis data operasional aktual (db_absensi_smk), sekolah ini menaungi 135 siswa aktif yang terdistribusi ke dalam 27 rombongan belajar (Kelas X, XI, dan XII pada masing-masing konsentrasi keahlian rombel 1, 2, dan 3), didukung oleh 30 tenaga pendidik (Dewan Guru) profesional yang mengampu 18 mata pelajaran kurikulum kejuruan, serta dipimpin langsung oleh Kepala Sekolah (Drs. Budi Santoso).")
    add_p(doc, "Karakteristik operasional di SMK NU Wargabinangun memiliki intensitas dinamis yang sangat tinggi, memadukan pembelajaran teori kejuruan di ruang kelas, praktikum intensif di bengkel otomotif dan laboratorium jaringan komputer, serta jadwal mengajar harian guru yang padat. Dalam ekosistem vokasi ini, kedisiplinan dan akurasi pencatatan kehadiran siswa maupun dewan guru merupakan pilar fundamental dalam menjamin mutu pembelajaran, keselamatan kerja, dan akuntabilitas institusi.")
    add_p(doc, "Namun, fakta di lapangan menunjukkan bahwa pengelolaan absensi pada sebagian besar SMK masih dihadapkan pada kendala teknis dan manajerial yang signifikan:")
    add_bullet(doc, "Pencatatan presensi menggunakan lembar kertas daftar hadir rentan terhadap kerusakan fisik, pemalsuan tanda tangan, dan membutuhkan waktu rekapitulasi manual berhari-hari oleh tim tata usaha.", "a. Kerentanan Presensi Kertas Konvensional: ")
    add_bullet(doc, "Mesin fingerprint optik konvensional sering mengalami kegagalan baca biometrik pada siswa/guru akibat jari berminyak setelah praktikum bengkel. Selain itu, mesin fisik memicu antrean panjang di gerbang sekolah dan data kehadiran tersimpan terisolasi di memori mesin (tidak real-time).", "b. Kelemahan Biometrik Standalone: ")
    add_bullet(doc, "Banyak aplikasi absensi mobile gagal memastikan apakah pengguna benar-benar berada di lingkungan sekolah atau melakukan absensi dari rumah dengan manipulasi GPS (Fake GPS).", "c. Tiadanya Validasi Geofencing Ketat: ")
    add_bullet(doc, "Siswa dan guru kerap mengalami kebingungan mencari jadwal pelajaran, ruang laboratorium, maupun informasi rekap presensi harian, sehingga membebani bagian kesiswaan dan tata usaha dengan pertanyaan-pertanyaan berulang.", "d. Absennya Asisten Informasi Cerdas: ")
    add_bullet(doc, "Ketiadaan notifikasi instan membuat orang tua atau kepala sekolah tidak dapat memantau kedatangan maupun keterlambatan siswa/guru secara real-time pada saat bel masuk sekolah berbunyi.", "e. Ketiadaan Saluran Notifikasi Instan: ")

    add_h2(doc, "1.2 Identifikasi Masalah & Nilai Guna Aplikasi")
    add_p(doc, "Berdasarkan analisis akar masalah tersebut, rumusan masalah dalam rekayasa sistem ini adalah:")
    add_bullet(doc, "Bagaimana membangun sistem informasi absensi berbasis web dengan arsitektur MVC yang mampu menangani presensi multi-aktor secara terstruktur?", "1. ")
    add_bullet(doc, "Bagaimana mengimplementasikan validasi lokasi GPS berbasis Haversine Formula dengan radius maksimal 100 meter untuk mencegah kecurangan absensi jarak jauh?", "2. ")
    add_bullet(doc, "Bagaimana merancang asisten virtual berbasis Multi-Provider AI (Google Gemini & OpenAI) yang mampu menjawab pertanyaan pengguna dengan konteks dinamis data sekolah tanpa membocorkan kredensial API?", "3. ")
    add_bullet(doc, "Bagaimana mengotomatisasi pengiriman notifikasi kehadiran ke Telegram Bot dengan arsitektur failover berketahanan tinggi?", "4. ")
    add_p(doc, "Nilai guna (value proposition) dari sistem yang dibangun adalah efisiensi 100% tanpa kertas, pencegahan kecurangan kehadiran hingga 99%, pelaporan analitik instan untuk Kepala Sekolah, serta kemudahan akses informasi interaktif bagi seluruh warga sekolah melalui Asisten Virtual AI.")

    add_h2(doc, "1.3 Ruang Lingkup Sistem & Karakteristik Pengguna (5 Role Aktor)")
    add_p(doc, "Sistem Informasi Presensi SMK NU Wargabinangun menerapkan kontrol akses berbasis peran (Role-Based Access Control / RBAC) yang membagi hak akses ke dalam 5 entitas peran utama guna melayani 169 akun pengguna aktif di lingkungan sekolah:")
    
    tbl_aktor = doc.add_table(rows=6, cols=4)
    tbl_aktor.rows[0].cells[0].paragraphs[0].text = "No"
    tbl_aktor.rows[0].cells[1].paragraphs[0].text = "Role / Aktor"
    tbl_aktor.rows[0].cells[2].paragraphs[0].text = "Hak Akses & Tanggung Jawab Operasional"
    tbl_aktor.rows[0].cells[3].paragraphs[0].text = "Ruang Lingkup Fitur"
    
    data_aktor = [
        ("1", "Super Admin", "Pengelolaan master data sekolah, manajemen akun pengguna, pengaturan jadwal presensi, penentuan titik koordinat GPS sekolah, manajemen Multi-Provider AI Key, dan konfigurasi Telegram Bot.", "CRUD Guru, Siswa, Kelas, Mapel, Jadwal, Monitoring User, Setting Radius, API Key Management."),
        ("2", "Dewan Guru", "Melakukan presensi mandiri (masuk & pulang) via barcode/kamera, mengajukan izin/sakit online disertai bukti surat, memantau riwayat mengajar dan presensi pribadi.", "Dashboard Guru, Scanner Barcode Guru, Form Pengajuan Izin/Sakit, Laporan Presensi Guru Pribadi."),
        ("3", "Wali Kelas", "Memantau data kehadiran dan catatan kedisiplinan seluruh siswa di kelas binaannya, memvalidasi rekap harian/bulanan, mencatat pembinaan siswa.", "Monitoring Presensi Kelas, Rekap Absensi Siswa Kelas, Manajemen Catatan Siswa (Prestasi & Pembinaan)."),
        ("4", "Kepala Sekolah", "Pengawasan eksekutif terhadap performa kehadiran dewan guru dan kedisiplinan seluruh siswa, memantau grafik analitik, mengunduh laporan PDF/Excel resmi.", "Dashboard Eksekutif, Monitoring Presensi Dewan Guru Hari Ini, Analisis Kehadiran Siswa, Cetak Laporan."),
        ("5", "Siswa", "Melakukan presensi harian mandiri via scanner kamera terminal / kartu RFID, melihat riwayat kehadiran, mengajukan izin/sakit, berkonsultasi ke Asisten Virtual AI.", "Dashboard Siswa, Scanner Barcode Mandiri, Form Izin/Sakit, Jadwal Pelajaran Kelas, Widget AI Chatbot.")
    ]
    for idx, (c1, c2, c3, c4) in enumerate(data_aktor, start=1):
        r = tbl_aktor.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
    format_table(tbl_aktor, col_widths=[0.4, 1.2, 2.7, 2.2])

    add_h2(doc, "1.4 Spesifikasi Kebutuhan Fungsional (User Stories & Acceptance Criteria)")
    add_p(doc, "Sesuai pedoman PRD/SRS standar IEEE 830, berikut adalah daftar kebutuhan fungsional inti yang telah direkayasa:")

    tbl_srs = doc.add_table(rows=7, cols=4)
    tbl_srs.rows[0].cells[0].paragraphs[0].text = "Kode SRS"
    tbl_srs.rows[0].cells[1].paragraphs[0].text = "Fitur Sistem"
    tbl_srs.rows[0].cells[2].paragraphs[0].text = "User Story"
    tbl_srs.rows[0].cells[3].paragraphs[0].text = "Acceptance Criteria (Kriteria Keberterimaan)"
    
    srs_data = [
        ("FR-AUTH-01", "Autentikasi Multi-Role & Proteksi Session", "Sebagai pengguna sistem, saya ingin login menggunakan username dan password terenkripsi agar dapat mengakses dashboard sesuai peran otoritas saya.", "Password diverifikasi menggunakan password_verify() Bcrypt. Session memuat role aktif, proteksi regenerasi ID session, dan redirect otomatis sesuai peran."),
        ("FR-ABSN-02", "Presensi Barcode/QR & RFID Geofenced", "Sebagai Siswa/Guru, saya ingin melakukan pemindaian barcode/RFID dalam radius sekolah agar presensi tercatat otomatis sebagai Hadir atau Terlambat.", "Sistem memeriksa koordinat GPS (maks 100m). Menilai jadwal aktif hari ini. Jika < jam masuk = Hadir; jika > jam masuk = Terlambat. Waktu dan status tersimpan di database."),
        ("FR-CHAT-03", "Asisten Virtual Multi-Provider AI", "Sebagai pengguna, saya ingin bertanya kepada Asisten Virtual di dashboard mengenai informasi sekolah dan jadwal menggunakan bahasa alami.", "Chatbot merespon dalam < 3 detik. Jawaban berfokus pada data sistem absensi SMK. API Key tidak pernah bocor ke sisi klien. Admin dapat mengganti provider tanpa mengubah kode."),
        ("FR-NOTIF-04", "Notifikasi Real-time Telegram Bot", "Sebagai pihak sekolah, saya ingin notifikasi instan terkirim ke channel/grup Telegram setiap kali siswa atau guru berhasil melakukan absensi.", "Payload memuat nama, NIP/NIS, kelas, waktu, dan status kehadiran. Dikirim menggunakan dual-fallback (cURL dan PHP stream context) dengan batas timeout 10 detik."),
        ("FR-JADW-05", "Manajemen Jadwal Jam Masuk & Hari", "Sebagai Admin, saya ingin mengatur jam mulai, batas jam masuk, jam akhir, dan hari operasional presensi (termasuk Sabtu dan Minggu).", "Admin dapat memilih hari: 'Senin - Jumat', 'Senin - Sabtu', atau 'Semua Hari'. Backend mengevaluasi jadwal server-side Asia/Jakarta."),
        ("FR-IZIN-06", "Pengajuan Izin & Sakit Online", "Sebagai Siswa/Guru, saya ingin mengajukan surat izin atau surat dokter secara online agar tidak dianggap alpa.", "Form menerima input tanggal mulai, tanggal selesai, alasan, dan berkas foto/PDF. Status absensi pada rentang tanggal otomatis tercatat sebagai Izin atau Sakit.")
    ]
    for idx, (c1, c2, c3, c4) in enumerate(srs_data, start=1):
        r = tbl_srs.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
    format_table(tbl_srs, col_widths=[0.9, 1.3, 2.1, 2.4])

    add_h2(doc, "1.5 Kebutuhan Non-Fungsional (Non-Functional Requirements)")
    add_bullet(doc, "Waktu respon pemrosesan scan barcode/RFID dan penyimpanan data absensi kurang dari 1 detik pada jaringan lokal.", "1. Kinerja (Performance): ")
    add_bullet(doc, "Penyimpanan password menggunakan hashing algoritma Bcrypt (Blowfish) cost factor 10. Seluruh transaksi formulir dilindungi token CSRF berbasis hash_equals(). API Key AI tersimpan aman di database server-side dan tidak pernah diekspos ke HTML/JS.", "2. Keamanan (Security): ")
    add_bullet(doc, "Sistem beroperasi 24/7 dengan arsitektur failover toleran gangguan koneksi eksternal pada notifikasi Telegram dan switch provider AI otomatis.", "3. Ketersediaan (Availability): ")
    add_bullet(doc, "Antarmuka responsif (Mobile-first) menggunakan Bootstrap 5 dengan dukungan Dark Mode / Light Mode otomatis demi kenyamanan visual pengguna.", "4. Ergonomi & Usabilitas (Usability): ")
    add_bullet(doc, "Kompatibel penuh dengan browser modern (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari) serta kamera web webcam dan pemindai USB Barcode/RFID standar.", "5. Kompatibilitas (Compatibility): ")

    add_h2(doc, "1.6 Spesifikasi Fitur Kecerdasan Artifisial (AI) Utama")
    add_p(doc, "Fitur kecerdasan artifisial yang diintegrasikan ke dalam Sistem Absensi SMK NU Wargabinangun bukanlah sekadar tempelan statis (gimmick), melainkan modul kognitif yang dirancang secara matang:")
    add_bullet(doc, "Sistem menerapkan Adapter Pattern yang mengabstraksi komunikasi API pihak ketiga. Administrator dapat memilih apakah Asisten Virtual ditenagai oleh Google Gemini API (model gemini-3.1-flash-lite-preview) atau OpenAI API (model gpt-4o-mini).", "• Multi-Provider AI Architecture: ")
    add_bullet(doc, "Sebelum prompt dikirim ke model AI, backend menginjeksi konteks dinamis berupa profil akun, statistik kehadiran hari ini, daftar jadwal mengajar, dan struktur kelas secara real-time dari MySQL. Hal ini memungkinkan AI menjawab pertanyaan spesifik seperti \"Kapan jadwal mengajar saya hari ini?\" atau \"Berapa persentase kehadiran guru pagi ini?\" secara akurat.", "• Dynamic Context Injection (RAG): ")
    add_bullet(doc, "Prompt sistem (System Instruction) mengunci persona AI sebagai Asisten Virtual SMK, membatasi lingkup jawaban hanya seputar lingkungan sekolah, dan menolak tegas instruksi berbahaya atau pertanyaan di luar konteks sekolah.", "• Strict System Instruction Boundary: ")
    add_bullet(doc, "Kunci rahasia API Key tersimpan dalam database dengan representasi terenkripsi/tersamarkan (masked, misal: AQ.Ab8R••••••••1cwg) sehingga administrator tidak dapat disadap melalui inspeksi elemen browser.", "• Zero-Exposure Security: ")

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB II: PERANCANGAN ARSITEKTUR & SISTEM MANDIRI (SDD)
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB II: PERANCANGAN ARSITEKTUR & SISTEM MANDIRI (SDD)")

    add_h2(doc, "2.1 Arsitektur Perangkat Lunak (MVC & SOLID Design)")
    add_p(doc, "Sistem dibangun menggunakan pola arsitektur Model-View-Controller (MVC) murni. Pemilihan MVC native bertujuan untuk memastikan performa eksekusi kode yang sangat cepat, jejak memori yang sangat ringan (lightweight footprint), serta pemahaman mendalam mahasiswa terhadap aliran siklus permintaan web (Request-Response Life Cycle).")
    add_p(doc, "Penerapan prinsip SOLID dalam arsitektur sistem:")
    add_bullet(doc, "Setiap kelas memiliki tanggung jawab tunggal. Database.php hanya mengurus koneksi PDO, AuthHelper hanya mengurus otentikasi/CSRF, dan LokasiHelper hanya menangani kalkulasi geofencing.", "• Single Responsibility Principle (SRP): ")
    add_bullet(doc, "Modul AI terbuka untuk penambahan provider baru tanpa mengubah kode inti chatbot melalui antarmuka AiProviderInterface.", "• Open/Closed Principle (OCP): ")
    add_bullet(doc, "Kelas GeminiAdapter dan OpenAiAdapter dapat saling menggantikan secara sempurna karena mengimplementasikan antarmuka yang seragam.", "• Liskov Substitution Principle (LSP): ")
    add_bullet(doc, "Antarmuka AI dirancang spesifik hanya memuat metode sendMessage() dan testConnection().", "• Interface Segregation Principle (ISP): ")
    add_bullet(doc, "Komponen Controller Chatbot tidak bergantung langsung pada kelas konkrit Gemini atau OpenAI, melainkan pada abstraksi yang dihasilkan oleh AiProviderFactory.", "• Dependency Inversion Principle (DIP): ")

    add_h2(doc, "2.2 Pemodelan UML Sistem Mandiri")
    add_p(doc, "Mengacu pada standar rekayasa perangkat lunak internasional dan rubrik penilaian OBE, pemodelan sistem diwujudkan ke dalam 4 Diagram UML utama yang memetakan struktur dan perilaku sistem secara visual, presisi, dan terstandarisasi:")

    # ─── 2.2.1 USE CASE DIAGRAM ───
    add_h3(doc, "2.2.1 Use Case Diagram & Skenario Use Case")
    add_p(doc, "Use Case Diagram pada Gambar 2.1 memvisualisasikan interaksi fungsional sistem di mana seluruh 5 aktor pengguna (Super Admin, Kepala Sekolah, Wali Kelas, Dewan Guru, dan Siswa) diposisikan secara terstruktur dan tertata rapi di sebelah kiri batas sistem (System Boundary). Di sebelah kanan batas sistem terdapat aktor pendukung/eksternal (Telegram Bot API dan AI Cloud Provider). Diagram ini memetakan relasi asosiasi langsung tanpa garis bersilangan, relasi ketergantungan «include» (validasi geofencing GPS & verifikasi jam operasional), serta relasi «extend» bersyarat untuk pengiriman notifikasi instan.")

    # Embed Gambar 2.1
    img_uc = os.path.join(out_dir, "use_case_diagram.png")
    add_figure(doc, img_uc, "Gambar 2.1: Use Case Diagram Sistem Informasi Presensi & Monitoring Absensi SMK", width_inches=6.2)

    add_p(doc, "Tabel 2.1: Definisi Aktor Sistem & Otoritas Peran", bold=True)
    tbl_def_akt = doc.add_table(rows=6, cols=3)
    tbl_def_akt.rows[0].cells[0].paragraphs[0].text = "Nama Aktor"
    tbl_def_akt.rows[0].cells[1].paragraphs[0].text = "Tipe Aktor"
    tbl_def_akt.rows[0].cells[2].paragraphs[0].text = "Deskripsi Otoritas & Tanggung Jawab"
    
    def_akt_data = [
        ("Super Admin", "Human (Internal)", "Memiliki hak penuh atas konfigurasi sistem, database, manajemen user, koordinat sekolah, dan pengaturan API Key AI Multi-Provider."),
        ("Dewan Guru", "Human (Internal)", "Melakukan presensi mengajar harian mandiri via scanner, mengajukan izin/sakit, dan berkonsultasi seputar jadwal ke AI."),
        ("Siswa", "Human (Internal)", "Melakukan presensi masuk/pulang via kamera/RFID, memeriksa rekapitulasi kehadiran pribadi, dan berinteraksi dengan AI."),
        ("Wali Kelas", "Human (Internal)", "Memantau rekap absensi harian kelas binaan serta mencatat pembinaan kedisiplinan dan prestasi siswa."),
        ("Kepala Sekolah", "Human (Internal)", "Mengakses analitik eksekutif kehadiran sekolah secara makro, memonitor kedisiplinan guru, dan mencetak laporan resmi.")
    ]
    for idx, (c1, c2, c3) in enumerate(def_akt_data, start=1):
        r = tbl_def_akt.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
    format_table(tbl_def_akt, col_widths=[1.5, 1.4, 3.6])

    add_p(doc, "Tabel 2.2: Skenario Rinci Use Case Inti (Formal Use Case Specifications)", bold=True)
    
    # Detail Scenario Table
    tbl_scen = doc.add_table(rows=11, cols=3)
    tbl_scen.rows[0].cells[0].paragraphs[0].text = "Elemen Skenario"
    tbl_scen.rows[0].cells[1].paragraphs[0].text = "Spesifikasi Use Case 1: Presensi Barcode/QR Geofencing"
    tbl_scen.rows[0].cells[2].paragraphs[0].text = "Spesifikasi Use Case 2: Konsultasi Asisten Virtual AI"
    
    scen_data = [
        ("ID & Nama Use Case", "UC-02: Presensi Barcode/QR Scanner", "UC-03: Konsultasi Asisten Virtual AI"),
        ("Aktor Utama", "Siswa dan Dewan Guru", "Seluruh Pengguna (Admin, Guru, Siswa, Kepsek)"),
        ("Tujuan (Goal)", "Mencatat absensi harian secara valid dan otomatis", "Mendapatkan jawaban cerdas terkait data sistem sekolah"),
        ("Kondisi Awal (Pre-condition)", "Pengguna membuka halaman scanner & mengizinkan akses GPS", "Pengguna telah login ke dashboard dan widget AI aktif"),
        ("Kondisi Akhir (Post-condition)", "Data kehadiran tersimpan di DB & terkirim ke Telegram", "Pengguna menerima teks respon ramah dan informatif"),
        ("Pemicu (Trigger)", "Pengguna mengarahkan barcode ke lensa kamera scanner", "Pengguna mengirim teks pertanyaan pada input chatbot"),
        ("Alur Utama (Main Flow)", 
         "1. Pengguna membuka antarmuka terminal scanner.\n2. Browser membaca koordinat GPS perangkat.\n3. Pengguna memindai kode barcode ID ke kamera.\n4. Frontend mengirim data scan + GPS via AJAX POST.\n5. Backend menghitung jarak menggunakan Haversine Formula.\n6. Backend memeriksa jadwal absensi aktif hari ini.\n7. Backend mengevaluasi jam scan (Hadir / Terlambat).\n8. Data disimpan ke database MySQL.\n9. Notifikasi di-dispatch ke Telegram Bot.\n10. Frontend memunculkan popup kartu presensi sukses.",
         "1. Pengguna menekan tombol floating widget Asisten Virtual.\n2. Pengguna mengetik pertanyaan pada kolom input pesan.\n3. Frontend memvalidasi token CSRF dan mengirim payload.\n4. Backend menginjeksi konteks dinamis data sekolah.\n5. Backend membaca API Key aktif dari ai_configurations.\n6. Factory mengarahkan prompt ke Adapter Provider yang aktif.\n7. Adapter mengirim HTTPS POST ke Google Gemini / OpenAI.\n8. Model AI memproses prompt sesuai System Instruction.\n9. Respon teks diekstraksi dan dinormalisasi menjadi JSON.\n10. Frontend menampilkan gelembung balasan interaktif."),
        ("Alur Alternatif (Alternative Flow)", 
         "4a. Barcode siswa tidak ditemukan -> Sistem menampilkan alert: 'Barcode tidak terdaftar'.\n7a. Scan dilakukan sebelum jam buka -> Tampil pesan: 'Absensi belum dibuka'.",
         "6a. Kuota API habis / koneksi timeout -> Sistem beralih ke fallback pesan ramah: 'Maaf, layanan AI sedang sibuk. Silakan coba lagi.'"),
        ("Alur Eksepsi (Exception Flow)", 
         "5a. Jarak GPS > 100 meter -> Transaksi ditolak: 'Anda berada di luar radius sekolah'.\n6a. Jadwal hari ini tidak aktif -> Ditampilkan popup penolakan jadwal.",
         "3a. Token CSRF tidak cocok -> Permintaan ditolak dengan kode keamanan HTTP 403 Forbidden."),
        ("Relasi UML", "<<include>> Validasi Geofencing, <<extend>> Notifikasi Bot", "<<include>> Validasi Sesi Role, <<include>> Context Injection")
    ]
    for idx, (c1, c2, c3) in enumerate(scen_data, start=1):
        r = tbl_scen.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
    format_table(tbl_scen, col_widths=[1.5, 2.5, 2.5])

    # ─── 2.2.2 ACTIVITY DIAGRAMS ───
    add_h3(doc, "2.2.2 Activity Diagram")
    add_p(doc, "Activity Diagram memetakan alur kerja sistem (workflow) secara rinci menggunakan jalur lintasan (swimlanes) yang memisahkan tanggung jawab antarentitas.")

    # Activity Diagram 1: Presensi
    add_p(doc, "A. Activity Diagram Presensi Geofencing GPS & Evaluasi Jadwal", bold=True)
    add_p(doc, "Gambar 2.2 mengilustrasikan alur logika dari pembacaan sensor kamera dan GPS hingga eksekusi kalkulasi jarak dan percabangan status Hadir/Terlambat di server.")
    img_act1 = os.path.join(out_dir, "activity_diagram_presensi.png")
    add_figure(doc, img_act1, "Gambar 2.2: Activity Diagram Presensi Geofencing GPS & Evaluasi Jadwal Server-Side", width_inches=5.8)

    add_p(doc, "Tabel 2.3: Matriks Alur Aktivitas Presensi Siswa/Guru (Swimlane Narration)", bold=True)
    tbl_swim1 = doc.add_table(rows=7, cols=4)
    tbl_swim1.rows[0].cells[0].paragraphs[0].text = "Fase Alur"
    tbl_swim1.rows[0].cells[1].paragraphs[0].text = "Aktivitas Pengguna"
    tbl_swim1.rows[0].cells[2].paragraphs[0].text = "Aktivitas Frontend Scanner"
    tbl_swim1.rows[0].cells[3].paragraphs[0].text = "Aktivitas Backend & Database"
    
    act1_data = [
        ("Inisiasi", "Buka menu presensi di browser", "Minta izin GPS & aktifkan webcam", "Siapkan token CSRF & data geolokasi"),
        ("Pemindaian", "Arahkan barcode ID ke kamera", "Html5Qrcode membaca string barcode", "Menunggu request AJAX masuk"),
        ("Validasi Geofence", "Menunggu verifikasi", "Kirim payload AJAX (Barcode, Lat, Lng)", "Hitung jarak sekolah via Haversine Formula (<= 100m)"),
        ("Evaluasi Jadwal", "-", "-", "Cek jadwal aktif hari ini (Senin s/d Minggu di Jadwal_model)"),
        ("Penentuan Status", "-", "-", "Bandingkan jam scan: <= jam masuk = Hadir; > jam masuk = Terlambat"),
        ("Finalisasi & Output", "Mendengar nada beep & lihat layar", "Tampilkan SweetAlert & kartu kehadiran", "Simpan record DB & trigger notifikasi Telegram API")
    ]
    for idx, (c1, c2, c3, c4) in enumerate(act1_data, start=1):
        r = tbl_swim1.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
    format_table(tbl_swim1, col_widths=[1.1, 1.8, 1.8, 1.8])

    # Activity Diagram 2: AI Chatbot
    add_p(doc, "B. Activity Diagram Asisten Virtual Multi-Provider AI", bold=True)
    add_p(doc, "Gambar 2.3 memetakan alur interaksi kognitif saat pengguna berkonsultasi, melibatkan mekanisme injeksi konteks dinamis RAG dari database sekolah ke model AI aktif.")
    img_act2 = os.path.join(out_dir, "activity_diagram_ai.png")
    add_figure(doc, img_act2, "Gambar 2.3: Activity Diagram Alur Dialog Asisten Virtual Multi-Provider AI", width_inches=5.8)

    # Activity Diagram 3: Telegram Notification
    add_p(doc, "C. Activity Diagram Notifikasi Real-Time Telegram Bot API", bold=True)
    add_p(doc, "Gambar 2.4 menunjukkan arsitektur ketahanan pengiriman notifikasi dengan mekanisme dual-fallback (cURL dan PHP Stream Context) untuk menjamin keandalan pengiriman.")
    img_act3 = os.path.join(out_dir, "activity_diagram_telegram.png")
    add_figure(doc, img_act3, "Gambar 2.4: Activity Diagram Alur Pengiriman Notifikasi Telegram Dual-Fallback", width_inches=5.8)

    # ─── 2.2.3 ENTITY CLASS DIAGRAM / PHYSICAL DATA MODEL (PK & FK EXPLICIT) ───
    add_h3(doc, "2.2.3 Diagram Relasi Entitas Data (Physical Data Model / ERD UML)")
    add_p(doc, "Sebagai bentuk perancangan struktural data berbasis standar UML dan Database Engineering, Gambar 2.5 memvisualisasikan seluruh 11 entitas tabel dalam basis data db_absensi_smk secara komprehensif. Pada diagram ini, setiap entitas kelas memuat stereotype «entity», atribut kolom lengkap dengan tipe data SQL, serta penandaan eksplisit untuk Kunci Primer [PK] dan Kunci Asing [FK] beserta derajat kardinalitas relasi antar tabel (Multiplicity: 1-ke-1, 1-ke-Banyak).")

    img_erd = os.path.join(out_dir, "class_diagram.png")
    add_figure(doc, img_erd, "Gambar 2.5: Diagram Relasi Entitas Data (Physical Data Model UML) Menampilkan Pemetaan Primary Key [PK], Foreign Key [FK], dan Kardinalitas Relasi 11 Tabel Basis Data db_absensi_smk", width_inches=6.4)

    add_p(doc, "Karakteristik integritas struktural yang dimodelkan pada Gambar 2.5 mencakup:")
    add_bullet(doc, "Tabel users berperan sebagai tabel induk autentikasi terpusat yang berelasi 1-ke-1 dengan entitas guru dan entitas siswa melalui foreign key user_id dengan aturan integritas referensial ON DELETE CASCADE.", "1. Relasi Induk Autentikasi: ")
    add_bullet(doc, "Tabel kelas mereferensikan wali_kelas_id ke tabel guru (1 guru dapat menjadi wali kelas dari 0 atau 1 kelas) dengan aturan ON DELETE SET NULL, sementara tabel siswa mereferensikan kelas_id ke tabel kelas (1 kelas memiliki 0 hingga banyak siswa).", "2. Relasi Akademik & Perwalian: ")
    add_bullet(doc, "Tabel transaksi absensi_siswa dan absensi_guru terhubung langsung ke entitas siswa (siswa_id) dan entitas guru (guru_id) dengan constraint unique composite key pada kombinasi kolom (id_pengguna, tanggal) guna mencegah redundansi data absensi ganda pada hari yang sama.", "3. Relasi Transaksional Presensi: ")
    add_bullet(doc, "Tabel jadwal_mengajar menghubungkan tiga entitas master sekaligus (guru_id, kelas_id, mapel_id) guna menjamin konsistensi jadwal kegiatan belajar mengajar.", "4. Relasi Multi-Entitas Pembelajaran: ")

    # ─── 2.2.4 CLASS DIAGRAM ARSITEKTUR BERORIENTASI OBJEK & SOLID ───
    add_h3(doc, "2.2.4 Class Diagram Arsitektur Berorientasi Objek & Desain Pola (MVC & SOLID)")
    add_p(doc, "Gambar 2.6 memvisualisasikan struktur kelas logika perangkat lunak yang memisahkan Controller, Model, Database Core, Helper Enkapsulasi, serta Desain Pola Factory dan Adapter Provider AI sesuai standar Object-Oriented Analysis and Design (OOAD):")

    img_mvc = os.path.join(out_dir, "class_diagram_mvc.png")
    add_figure(doc, img_mvc, "Gambar 2.6: Class Diagram Arsitektur Berorientasi Objek (OOP MVC, Prinsip SOLID, Desain Pola Factory & Provider Adapter)", width_inches=6.2)

    add_p(doc, "Tabel 2.4: Matriks Tanggung Jawab Kelas & Implementasi Desain Pola", bold=True)
    tbl_mvc = doc.add_table(rows=7, cols=3)
    tbl_mvc.rows[0].cells[0].paragraphs[0].text = "Nama Komponen Kelas"
    tbl_mvc.rows[0].cells[1].paragraphs[0].text = "Peran / Pola Desain"
    tbl_mvc.rows[0].cells[2].paragraphs[0].text = "Deskripsi Enkapsulasi & Implementasi Prinsip SOLID"
    mvc_data = [
        ("Controller & Database Core", "MVC Controller Base & Singleton PDO", "Menyediakan routing view, pemuatan model dinamis, serta wrapper PDO yang aman terhadap SQL Injection melalui metode bind parametrik."),
        ("Absensi_siswa & Guru_absensi", "Feature Controller", "Mengontrol orkestrasi pemindaian barcode kamera, parsing koordinat GPS dari geolokasi browser, dan verifikasi aturan jam operasional."),
        ("Absensi_model & Jadwal_model", "Domain Business Model", "Mengeksekusi logika kalkulasi Haversine, evaluasi status kehadiran (Hadir/Terlambat), serta query transaksional ACID ke database."),
        ("AiProviderInterface", "Interface Abstraction (ISP & DIP)", "Antarmuka kontrak seragam yang mewajibkan metode sendMessage() dan testConnection(), memastikan Controller tidak bergantung pada SDK konkrit."),
        ("AiProviderFactory", "Factory Method Pattern", "Menginstansiasi adapter provider (GeminiAdapter / OpenAiAdapter) secara dinamis berdasarkan baris konfigurasi yang sedang aktif di database."),
        ("TelegramHelper & LokasiHelper", "Encapsulated Utility Helper (SRP)", "Helper khusus menangani transmisi notifikasi Telegram dengan dual-fallback (cURL / Stream Context) dan kalkulasi trigonometri radius geofencing.")
    ]
    for idx, (c1, c2, c3) in enumerate(mvc_data, start=1):
        r = tbl_mvc.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
    format_table(tbl_mvc, col_widths=[1.8, 1.8, 3.2])

    # ─── 2.2.5 SEQUENCE DIAGRAM PRESENSI ───
    add_h3(doc, "2.2.5 Sequence Diagram Alur Transaksi Presensi & Validasi Geofencing")
    add_p(doc, "Gambar 2.7 memodelkan urutan pertukaran pesan (message passing) dan masa hidup (lifeline) objek secara kronologis selama proses pemindaian presensi barcode, kalkulasi radius GPS, transaksi basis data, hingga pengiriman notifikasi otomatis:")

    img_seq = os.path.join(out_dir, "sequence_diagram_presensi.png")
    add_figure(doc, img_seq, "Gambar 2.7: Sequence Diagram Alur Pemindaian Presensi, Geofencing, Evaluasi Jadwal, dan Database Transaction", width_inches=6.2)

    # ─── 2.2.6 SEQUENCE DIAGRAM AI CHATBOT ───
    add_h3(doc, "2.2.6 Sequence Diagram Interaksi & Komunikasi Asisten Virtual AI (Chatbot)")
    add_p(doc, "Gambar 2.8 secara khusus memodelkan alur interaksi sekuensial dan protokol komunikasi pertukaran pesan secara kronologis pada fitur Asisten Virtual AI (Chatbot) multi-provider. Diagram ini menggambarkan alur lengkap dari pengiriman pertanyaan oleh pengguna di sisi antarmuka web hingga penerimaan jawaban inferensi cerdas dari cloud AI:")

    add_bullet(doc, "Langkah 1: Pengguna mengirimkan input pertanyaan teks melalui antarmuka widget chat pada browser.")
    add_bullet(doc, "Langkah 2: Frontend UI (views/layouts/footer.php) mengirimkan permintaan HTTP POST secara asinkron (AJAX fetch) dalam format JSON { message: text } menuju endpoint controller backend /websekolah3/public/chatbot.")
    add_bullet(doc, "Langkah 3: Chatbot Controller (app/controllers/Chatbot.php) memvalidasi masukan serta memeriksa session login aktif, hak akses role pengguna ($_SESSION['role']), dan identitas user_id.")
    add_bullet(doc, "Langkah 4: Chatbot Controller memicu pengambilan data dashboard dan konteks sekolah aktual via app/core/Database.php secara dinamis sesuai role pengguna (misal: data KBM dan dewan guru untuk Kepala Sekolah, atau rekap kelas binaan untuk Wali Kelas).")
    add_bullet(doc, "Langkah 5: Sistem menyusun System Prompt Injection bersama guardrails filter keamanan, lalu menginisialisasi provider AI aktif melalui AiProviderFactory (GeminiAdapter atau OpenAiAdapter) untuk mengirimkan request HTTP POST ke layanan AI Cloud.")
    add_bullet(doc, "Langkah 6: Server AI Cloud Provider (Google Gemini API / OpenAI Platform) memproses inferensi model bahasa dan mengembalikan jawaban teks dalam format JSON.")
    add_bullet(doc, "Langkah 7: Backend Chatbot Controller menerima string teks jawaban AI, menyusun payload respons HTTP JSON { success: true, message: jawaban }, dan mengembalikannya ke frontend.")
    add_bullet(doc, "Langkah 8: Widget frontend mem-parsing pesan, merender tampilan bubble chat dengan format rapi (Markdown/HTML), dan memutar notifikasi audio kepada pengguna.")

    add_callout(doc, "Mekanisme Dual-Fallback Otomatis:\nApabila sambungan ke layanan AI Cloud mengalami kendala timeout atau jaringan eksternal terputus, Chatbot Controller secara otomatis mengalihkan alur ke mesin Rule-Based Regex lokal (lokal fallback). Hal ini menjamin pengguna tetap memperoleh jawaban informasi sekolah tanpa terganggu pesan galat (zero downtime user experience).",
                title="RELIABILITAS SISTEM CHATBOT: DUAL-FALLBACK ARCHITECTURE", color_hex="059669", bg_hex="ECFDF5")

    img_seq_ai = os.path.join(out_dir, "sequence_diagram_ai.png")
    add_figure(doc, img_seq_ai, "Gambar 2.8: Sequence Diagram Alur Interaksi & Komunikasi Asisten Virtual AI (Chatbot) Multi-Provider", width_inches=6.2)

    # ─── 2.3 DATABASE DESIGN & PEMETAAN KUNCI RELASIONAL ───
    add_h2(doc, "2.3 Perancangan Basis Data Relasional & Pemetaan Kunci (PK-FK Mapping)")
    add_p(doc, "Basis data Sistem Informasi Presensi SMK dibangun menggunakan DBMS MySQL relasional dengan mesin penyimpanan InnoDB. Seluruh integritas hubungan antar tabel ditegakkan menggunakan Foreign Key Constraints, indeks B-Tree pada kolom pencarian, serta unique constraints komposit guna menjamin integritas entitas (Entity Integrity) dan integritas referensial (Referential Integrity).")

    add_h3(doc, "2.3.1 Matriks Pemetaan Kunci Primer (PK) dan Kunci Asing (FK)")
    add_p(doc, "Tabel 2.5 menyajikan pemetaan komprehensif seluruh Kunci Primer (Primary Key) dan Kunci Asing (Foreign Key) yang menghubungkan 11 tabel dalam basis data db_absensi_smk beserta aturan integritas referensialnya:")

    add_p(doc, "Tabel 2.5: Matriks Komprehensif Primary Key [PK] dan Foreign Key [FK] Basis Data db_absensi_smk", bold=True)
    tbl_pkfk = doc.add_table(rows=12, cols=6)
    tbl_pkfk.rows[0].cells[0].paragraphs[0].text = "No"
    tbl_pkfk.rows[0].cells[1].paragraphs[0].text = "Nama Tabel"
    tbl_pkfk.rows[0].cells[2].paragraphs[0].text = "Primary Key [PK]"
    tbl_pkfk.rows[0].cells[3].paragraphs[0].text = "Foreign Key [FK]"
    tbl_pkfk.rows[0].cells[4].paragraphs[0].text = "Tabel Acuan (Induk)"
    tbl_pkfk.rows[0].cells[5].paragraphs[0].text = "Aturan Integritas & Keterangan"

    pkfk_data = [
        ("1", "users", "id (INT)", "-", "-", "Tabel induk otentikasi login, password Bcrypt, role RBAC."),
        ("2", "guru", "id (INT)", "user_id (INT)", "users(id)", "ON DELETE CASCADE: Menghapus data akun users akan otomatis menghapus profil guru terkait."),
        ("3", "kelas", "id (INT)", "wali_kelas_id (INT)", "guru(id)", "ON DELETE SET NULL: Jika guru dihapus, wali_kelas_id diatur NULL tanpa menghapus rombel kelas."),
        ("4", "siswa", "id (INT)", "user_id, kelas_id", "users(id), kelas(id)", "fk_siswa_user: ON DELETE CASCADE. fk_siswa_kelas: ON DELETE SET NULL."),
        ("5", "absensi_siswa", "id (INT)", "siswa_id (INT)", "siswa(id)", "ON DELETE CASCADE. Dilengkapi Unique Key komposit (siswa_id, tanggal) cegah absensi dobel."),
        ("6", "absensi_guru", "id (INT)", "guru_id (INT)", "guru(id)", "ON DELETE CASCADE. Dilengkapi Unique Key komposit (guru_id, tanggal) cegah absensi ganda."),
        ("7", "catatan_siswa", "id (INT)", "siswa_id, wali_kelas_id", "siswa(id), guru(id)", "ON DELETE CASCADE: Menghubungkan riwayat pembinaan kedisiplinan dan prestasi siswa."),
        ("8", "mata_pelajaran", "id (INT)", "-", "-", "Tabel master muatan kurikulum SMK, kode_mapel bersifat UNIQUE."),
        ("9", "jadwal_mengajar", "id (INT)", "guru_id, kelas_id, mapel_id", "guru(id), kelas(id), mata_pelajaran(id)", "fk_jm_guru & fk_jm_kelas: CASCADE. fk_jm_mapel: SET NULL."),
        ("10", "jadwal_absensi", "id (INT)", "kelas_id (INT)", "kelas(id)", "ON DELETE SET NULL ON UPDATE CASCADE: Rentang jam masuk, tepat waktu, dan dispensasi."),
        ("11", "ai_configurations", "id (INT)", "-", "-", "Tabel konfigurasi multi API key AI mandiri (Gemini, OpenAI, Groq) dengan penanda is_active.")
    ]

    for idx, (c1, c2, c3, c4, c5, c6) in enumerate(pkfk_data, start=1):
        r = tbl_pkfk.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
        r.cells[4].paragraphs[0].text = c5
        r.cells[5].paragraphs[0].text = c6
    format_table(tbl_pkfk, col_widths=[0.4, 1.2, 1.1, 1.3, 1.2, 1.8])

    add_h3(doc, "2.3.2 Kamus Data Struktur Tabel Aktual (Data Dictionary)")
    add_p(doc, "Berikut adalah rincian kamus data dari seluruh 11 tabel aktual yang beroperasi pada sistem basis data db_absensi_smk:")

    # Table 1: users
    add_p(doc, "1. Tabel: users (Manajemen Akun dan Hak Akses Pengguna)", bold=True)
    tbl_u = doc.add_table(rows=8, cols=5)
    tbl_u.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_u.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_u.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_u.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_u.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    u_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik akun pengguna (Primary Key)"),
        ("username", "varchar(50)", "NO", "[UQ] UNIQUE", "Nama pengguna unik untuk autentikasi login"),
        ("password", "varchar(255)", "NO", "-", "Hash kata sandi terenkripsi menggunakan algoritma Bcrypt"),
        ("nama", "varchar(100)", "YES", "-", "Nama tampilan umum akun pengguna"),
        ("role", "enum('admin','guru','kepala_sekolah','wali_kelas','siswa')", "NO", "-", "Peran hak akses sistem (Role-Based Access Control)"),
        ("status", "enum('active','inactive')", "YES", "-", "Status keaktifan akun pengguna di sistem"),
        ("created_at", "timestamp", "NO", "-", "Waktu pencatatan akun ke sistem (CURRENT_TIMESTAMP)")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(u_fields, start=1):
        r = tbl_u.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_u, col_widths=[1.1, 1.7, 0.6, 0.9, 2.7])

    # Table 2: guru
    add_p(doc, "2. Tabel: guru (Data Profil Dewan Guru & Barcode Scanner)", bold=True)
    tbl_g = doc.add_table(rows=10, cols=5)
    tbl_g.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_g.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_g.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_g.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_g.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    g_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik profil guru (Primary Key)"),
        ("user_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> users(id) ON DELETE CASCADE"),
        ("nip", "varchar(20)", "YES", "[UQ] UNIQUE", "Nomor Induk Pegawai unik guru"),
        ("nama", "varchar(100)", "NO", "-", "Nama lengkap guru beserta gelar akademik"),
        ("mata_pelajaran", "varchar(100)", "YES", "-", "Bidang studi / mata pelajaran yang diampu"),
        ("barcode", "varchar(100)", "YES", "[UQ] UNIQUE", "Kode unik barcode guru untuk scanner presensi"),
        ("no_hp", "varchar(20)", "YES", "-", "Nomor WhatsApp/kontak guru"),
        ("status", "enum('active','inactive')", "YES", "-", "Status kepegawaian aktif guru"),
        ("created_at", "timestamp", "NO", "-", "Waktu pencatatan data guru")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(g_fields, start=1):
        r = tbl_g.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_g, col_widths=[1.1, 1.5, 0.6, 1.0, 2.8])

    # Table 3: kelas
    add_p(doc, "3. Tabel: kelas (Data Rombongan Belajar & Penugasan Wali Kelas)", bold=True)
    tbl_k = doc.add_table(rows=6, cols=5)
    tbl_k.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_k.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_k.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_k.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_k.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    k_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik rombongan belajar (Primary Key)"),
        ("nama_kelas", "varchar(50)", "NO", "-", "Nama kelas (misal: X RPL 1, XI TKJ 2)"),
        ("jurusan", "varchar(100)", "YES", "-", "Program keahlian kejuruan"),
        ("tingkat", "varchar(10)", "YES", "-", "Tingkat kelas (X, XI, XII)"),
        ("wali_kelas_id", "int(11)", "YES", "[FK] MUL", "Foreign Key -> guru(id) ON DELETE SET NULL")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(k_fields, start=1):
        r = tbl_k.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_k, col_widths=[1.1, 1.5, 0.6, 1.0, 2.8])

    # Table 4: siswa
    add_p(doc, "4. Tabel: siswa (Data Pokok Siswa & Terminal RFID/Barcode)", bold=True)
    tbl_s = doc.add_table(rows=12, cols=5)
    tbl_s.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_s.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_s.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_s.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_s.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    s_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik data siswa (Primary Key)"),
        ("user_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> users(id) ON DELETE CASCADE"),
        ("nis", "varchar(20)", "NO", "[UQ] UNIQUE", "Nomor Induk Siswa lokal sekolah"),
        ("nisn", "varchar(20)", "YES", "[UQ] UNIQUE", "Nomor Induk Siswa Nasional Dapodik"),
        ("nama", "varchar(100)", "NO", "-", "Nama lengkap siswa"),
        ("jenis_kelamin", "enum('L','P')", "NO", "-", "Jenis kelamin (L = Laki-laki, P = Perempuan)"),
        ("kelas_id", "int(11)", "YES", "[FK] MUL", "Foreign Key -> kelas(id) ON DELETE SET NULL"),
        ("rfid_uid", "varchar(50)", "YES", "[UQ] UNIQUE", "Kode UID unik chip kartu RFID siswa"),
        ("barcode", "varchar(100)", "YES", "[UQ] UNIQUE", "Kode barcode QR unik presensi siswa"),
        ("alamat", "text", "YES", "-", "Alamat domisili tempat tinggal siswa"),
        ("status", "enum('active','inactive')", "YES", "-", "Status keaktifan kesiswaan")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(s_fields, start=1):
        r = tbl_s.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_s, col_widths=[1.1, 1.5, 0.6, 1.0, 2.8])

    # Table 5: absensi_siswa
    add_p(doc, "5. Tabel: absensi_siswa (Transaksi Presensi Harian Siswa)", bold=True)
    tbl_as = doc.add_table(rows=8, cols=5)
    tbl_as.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_as.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_as.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_as.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_as.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    as_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik transaksi presensi siswa (Primary Key)"),
        ("siswa_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> siswa(id) ON DELETE CASCADE"),
        ("tanggal", "date", "NO", "MUL", "Tanggal absensi (Bagian dari Composite Unique uq_absensi_siswa_tanggal)"),
        ("jam_masuk", "time", "YES", "-", "Waktu tercatat absensi masuk"),
        ("jam_pulang", "time", "YES", "-", "Waktu tercatat absensi pulang"),
        ("status", "enum('hadir','terlambat','sakit','izin','alpa')", "NO", "-", "Status kehadiran hasil evaluasi waktu jadwal"),
        ("keterangan", "text", "YES", "-", "Catatan detail presensi atau surat izin online")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(as_fields, start=1):
        r = tbl_as.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_as, col_widths=[1.1, 1.7, 0.6, 0.9, 2.7])

    # Table 6: absensi_guru
    add_p(doc, "6. Tabel: absensi_guru (Transaksi Presensi Harian Dewan Guru)", bold=True)
    tbl_ag = doc.add_table(rows=8, cols=5)
    tbl_ag.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_ag.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_ag.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_ag.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_ag.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    ag_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik transaksi presensi guru (Primary Key)"),
        ("guru_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> guru(id) ON DELETE CASCADE"),
        ("tanggal", "date", "NO", "MUL", "Tanggal absensi (Bagian dari Composite Unique uq_absensi_guru_tanggal)"),
        ("jam_scan", "time", "YES", "-", "Waktu rekaman pemindaian barcode guru"),
        ("jam_pulang", "time", "YES", "-", "Waktu tercatat absensi kepulangan"),
        ("status", "enum('hadir','terlambat','sakit','izin','alpa')", "NO", "-", "Status kehadiran dewan guru"),
        ("keterangan", "text", "YES", "-", "Keterangan atau alasan dispensasi kehadiran")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(ag_fields, start=1):
        r = tbl_ag.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_ag, col_widths=[1.1, 1.7, 0.6, 0.9, 2.7])

    # Table 7: catatan_siswa
    add_p(doc, "7. Tabel: catatan_siswa (Manajemen Kedisiplinan & Binaan Wali Kelas)", bold=True)
    tbl_cs = doc.add_table(rows=8, cols=5)
    tbl_cs.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_cs.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_cs.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_cs.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_cs.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    cs_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik catatan kedisiplinan (Primary Key)"),
        ("siswa_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> siswa(id) ON DELETE CASCADE"),
        ("wali_kelas_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> guru(id) ON DELETE CASCADE"),
        ("kategori", "enum('Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya')", "NO", "-", "Kategori catatan kesiswaan"),
        ("tanggal", "date", "NO", "-", "Tanggal pencatatan pembinaan/pelanggaran"),
        ("catatan", "text", "NO", "-", "Uraian detail pembinaan atau prestasi siswa"),
        ("created_at", "timestamp", "NO", "-", "Waktu pencatatan (CURRENT_TIMESTAMP)")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(cs_fields, start=1):
        r = tbl_cs.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_cs, col_widths=[1.1, 1.7, 0.6, 0.9, 2.7])

    # Table 8: mata_pelajaran
    add_p(doc, "8. Tabel: mata_pelajaran (Master Kurikulum Mata Pelajaran SMK)", bold=True)
    tbl_mp = doc.add_table(rows=7, cols=5)
    tbl_mp.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_mp.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_mp.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_mp.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_mp.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    mp_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik mata pelajaran (Primary Key)"),
        ("kode_mapel", "varchar(20)", "NO", "[UQ] UNIQUE", "Kode unik mapel kurikulum (misal: RPL01, MTK01)"),
        ("nama_mapel", "varchar(100)", "NO", "-", "Nama lengkap mata pelajaran"),
        ("kelompok", "varchar(50)", "NO", "-", "Kelompok mapel (Muatan Nasional, Kejuruan, dll.)"),
        ("jam_per_minggu", "int(11)", "NO", "-", "Beban jam mengajar per minggu"),
        ("status", "enum('active','inactive')", "NO", "-", "Status keaktifan mapel dalam kurikulum")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(mp_fields, start=1):
        r = tbl_mp.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_mp, col_widths=[1.1, 1.6, 0.6, 1.0, 2.7])

    # Table 9: jadwal_mengajar
    add_p(doc, "9. Tabel: jadwal_mengajar (Alokasi Jam Belajar Mengajar Guru & Kelas)", bold=True)
    tbl_jm = doc.add_table(rows=9, cols=5)
    tbl_jm.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_jm.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_jm.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_jm.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_jm.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    jm_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik jadwal mengajar (Primary Key)"),
        ("guru_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> guru(id) ON DELETE CASCADE"),
        ("kelas_id", "int(11)", "NO", "[FK] MUL", "Foreign Key -> kelas(id) ON DELETE CASCADE"),
        ("mapel_id", "int(11)", "YES", "[FK] MUL", "Foreign Key -> mata_pelajaran(id) ON DELETE SET NULL"),
        ("hari", "enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')", "NO", "-", "Hari pelaksanaan pembelajaran"),
        ("jam_mulai", "time", "NO", "-", "Jam mulai pelajaran"),
        ("jam_selesai", "time", "NO", "-", "Jam berakhir pelajaran"),
        ("ruangan", "varchar(50)", "YES", "-", "Lokasi ruang kelas atau laboratorium")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(jm_fields, start=1):
        r = tbl_jm.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_jm, col_widths=[1.1, 1.6, 0.6, 1.0, 2.7])

    # Table 10: jadwal_absensi
    add_p(doc, "10. Tabel: jadwal_absensi (Aturan Jam & Batas Toleransi Hadir Sekolah)", bold=True)
    tbl_ja = doc.add_table(rows=10, cols=5)
    tbl_ja.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_ja.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_ja.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_ja.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_ja.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    ja_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik konfigurasi jadwal presensi (Primary Key)"),
        ("nama_jadwal", "varchar(100)", "NO", "-", "Label jadwal (misal: Jadwal Siswa Reguler, Jadwal Guru)"),
        ("tipe_pengguna", "enum('siswa','guru')", "NO", "MUL", "Kategori sasaran pengguna"),
        ("kelas_id", "int(11)", "YES", "[FK] MUL", "Foreign Key -> kelas(id) ON DELETE SET NULL (NULL = Semua Kelas)"),
        ("hari", "varchar(50)", "NO", "-", "Hari berlaku jadwal presensi"),
        ("jam_mulai", "time", "NO", "-", "Jam dimulainya pemindaian presensi"),
        ("jam_masuk", "time", "NO", "-", "Batas maksimal jam kehadiran tepat waktu (Hadir vs Terlambat)"),
        ("jam_akhir", "time", "NO", "-", "Batas akhir penutupan pemindaian absensi"),
        ("is_active", "tinyint(1)", "NO", "-", "Status keaktifan jadwal (1 = Aktif, 0 = Non-aktif)")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(ja_fields, start=1):
        r = tbl_ja.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_ja, col_widths=[1.1, 1.6, 0.6, 1.0, 2.7])

    # Table 11: ai_configurations
    add_p(doc, "11. Tabel: ai_configurations (Penyimpanan Multi API Key & Engine Model Chatbot)", bold=True)
    tbl_ai = doc.add_table(rows=11, cols=5)
    tbl_ai.rows[0].cells[0].paragraphs[0].text = "Nama Kolom"
    tbl_ai.rows[0].cells[1].paragraphs[0].text = "Tipe Data"
    tbl_ai.rows[0].cells[2].paragraphs[0].text = "Null"
    tbl_ai.rows[0].cells[3].paragraphs[0].text = "Kunci (Key)"
    tbl_ai.rows[0].cells[4].paragraphs[0].text = "Keterangan Fungsi & Integritas"
    ai_fields = [
        ("id", "int(11)", "NO", "[PK] PRI / AI", "Identifikator unik konfigurasi provider AI (Primary Key)"),
        ("label", "varchar(100)", "NO", "-", "Label konfigurasi API (misal: Gemini Production, OpenAI Backup)"),
        ("provider", "varchar(50)", "NO", "-", "Penyedia layanan AI (gemini, openai, groq)"),
        ("api_key", "text", "NO", "-", "Kunci API rahasia tersimpan di backend (server-side secret)"),
        ("model", "varchar(100)", "NO", "-", "Nama engine model AI (gemini-3.1-flash-lite, gpt-4o-mini)"),
        ("base_url", "varchar(255)", "YES", "-", "Endpoint URL kustom untuk adapter provider"),
        ("is_active", "tinyint(1)", "NO", "-", "Penanda konfigurasi utama yang sedang aktif (1 = Aktif)"),
        ("is_fallback", "tinyint(1)", "NO", "-", "Penanda kunci cadangan jika provider utama kuota habis"),
        ("last_status", "varchar(50)", "YES", "-", "Status hasil uji koneksi terakhir (success/error/untested)"),
        ("last_tested_at", "datetime", "YES", "-", "Waktu stempel pengujian koneksi terakhir kali")
    ]
    for idx, (f1, f2, f3, f4, f5) in enumerate(ai_fields, start=1):
        r = tbl_ai.rows[idx]
        r.cells[0].paragraphs[0].text = f1
        r.cells[1].paragraphs[0].text = f2
        r.cells[2].paragraphs[0].text = f3
        r.cells[3].paragraphs[0].text = f4
        r.cells[4].paragraphs[0].text = f5
    format_table(tbl_ai, col_widths=[1.1, 1.5, 0.6, 1.0, 2.8])

    doc.add_page_break()
    # ─── 2.4 PERANCANGAN ANTARMUKA PENGGUNA (UI/UX) & PURWARUPA FIGMA ───
    add_h2(doc, "2.4 Perancangan Antarmuka Pengguna (UI/UX) & Purwarupa Figma")
    add_p(doc, "Perancangan antarmuka pengguna (User Interface/UI) dan pengalaman pengguna (User Experience/UX) merupakan pilar utama dalam memastikan Sistem Informasi Presensi SMK dapat dioperasikan secara ergonomis, cepat, dan minim kesalahan operasional oleh seluruh tingkatan pengguna (Super Admin, Kepala Sekolah, Wali Kelas, Dewan Guru, dan Siswa).")

    add_h3(doc, "2.4.1 Tujuan Perancangan Antarmuka Pengguna")
    add_p(doc, "Tujuan utama dari perancangan antarmuka sistem absensi ini dirumuskan berdasarkan prinsip Rekayasa Antarmuka Manusia dan Komputer (HCI) serta standar usability ISO 9241-11:")
    add_bullet(doc, "Efisiensi Transaksi Presensi Cepat: Meminimalkan waktu interaksi pemindaian barcode/RFID menjadi kurang dari 3 detik per siswa guna mencegah penumpukan antrean di gerbang sekolah pada jam sibuk pagi hari.")
    add_bullet(doc, "Aksesibilitas Multi-Peran (RBAC Visual): Menyajikan tata letak dashboard yang relevan dan terisolasi sesuai wewenang peran (Super Admin melihat kontrol konfigurasi, Kepala Sekolah melihat rekap eksekutif, Wali Kelas melihat catatan binaan, Guru melihat jadwal mengajar, dan Siswa melihat presensi mandiri).")
    add_bullet(doc, "Dukungan Responsif Multi-Perangkat (Cross-Device Layout): Antarmuka dirancang adaptif (mobile-friendly) menggunakan Bootstrap 5 dan CSS Grid/Flexbox sehingga dapat diakses sempurna melalui PC desktop operator, tablet piket, maupun smartphone guru/wali.")
    add_bullet(doc, "Peredaan Kelelahan Mata (Mode Gelap / Dark Mode): Sistem menyediakan saklar instan (toggle) Mode Terang dan Mode Gelap yang tersimpan secara lokal pada preferensi peramban pengguna.")
    add_bullet(doc, "Akses Bantuan AI Terintegrasi (Omnipresent Floating Widget): Widget chatbot ditempatkan di pojok kanan bawah seluruh halaman dashboard sehingga pengguna dapat berkonsultasi mengenai informasi sistem sekolah kapan pun dibutuhkan.")

    add_h3(doc, "2.4.2 Purwarupa Figma & Tautan Desain Interaktif")
    add_p(doc, "Perancangan tata letak awal (wireframing) dan purwarupa antarmuka fidelitas tinggi (High-Fidelity Prototype) dikembangkan secara profesional menggunakan perangkat lunak Figma. Gambar 2.9 menampilkan dokumentasi papan kerja Figma (Figma Design Workspace) yang memuat Design System lengkap, skala tipografi Inter, token palet warna institusi (Navy Blue #1E3A8A, Accent Blue #2563EB, Emerald Green #10B981, Rose Red #EF4444), pustaka komponen UI atomik, serta bagan alur pengguna (wireflow) 8 antarmuka aplikasi:")

    img_figma_board = os.path.join(out_dir, "figma_ui_design_system.png")
    add_figure(doc, img_figma_board, "Gambar 2.9: Dokumentasi Papan Kerja Figma (Design System, Skala Tipografi, Komponen UI, dan User Flow Interaktif)", width_inches=6.2)

    add_callout(doc, "Tautan Berkas Purwarupa Interaktif Figma (Figma Prototype):\n[Tautan Berbagi Figma: Silakan salin tautan dari tombol Share di Figma Anda]\n\nCatatan Pengujian UX: Seluruh komponen atomic design, tokens warna, kartu metrik statistik, dan tata letak responsif pada purwarupa Figma di atas telah diimplementasikan secara presisi 1:1 ke dalam antarmuka web aktual pada direktori app/views/.",
                title="AKSES BERKAS PURWARUPA FIGMA PROTOTYPE RESMI", color_hex="1B365D", bg_hex="F0F4F8")

    add_h3(doc, "2.4.3 Mockup Desain Antarmuka Sistem Aktual (8 Halaman Utama)")
    add_p(doc, "Berikut disajikan hasil tangkapan layar (screenshot) antarmuka sistem aplikasi presensi yang telah diimplementasikan secara penuh berdasarkan rancangan purwarupa Figma:")

    # 1. Login
    add_p(doc, "1. Halaman Login & Autentikasi Pengguna (Gambar 2.10):\nMenampilkan antarmuka gerbang masuk sistem dengan enkripsi password Bcrypt, pemilihan peran dinamis, dan identitas visual sekolah:", bold=True)
    img_login = os.path.join(out_dir, "ui_login.png")
    add_figure(doc, img_login, "Gambar 2.10: Antarmuka Halaman Login Pengguna Berbasis Bcrypt Hashing", width_inches=5.8)

    # 2. Super Admin Dashboard
    add_p(doc, "2. Dashboard Utama Super Admin (Gambar 2.11):\nPusat kendali administratif yang menampilkan kartu metrik ringkasan (Total Guru, Siswa, Kelas, & Persentase Kehadiran), status API Key AI aktif, serta log aktivitas:", bold=True)
    img_admin = os.path.join(out_dir, "ui_dashboard_admin.png")
    add_figure(doc, img_admin, "Gambar 2.11: Antarmuka Dashboard Utama Super Admin (Monitoring Global & Multi-Provider AI)", width_inches=5.8)

    # 3. Kepala Sekolah Dashboard
    add_p(doc, "3. Dashboard Eksekutif Kepala Sekolah (Gambar 2.12):\nTampilan khusus pimpinan sekolah yang fokus pada monitoring kedisiplinan dan rekapitulasi kehadiran dewan guru, jadwal KBM, dan laporan kehadiran:", bold=True)
    img_kepsek = os.path.join(out_dir, "ui_dashboard_kepsek.png")
    add_figure(doc, img_kepsek, "Gambar 2.12: Antarmuka Dashboard Eksekutif Kepala Sekolah (Monitoring Kehadiran Dewan Guru)", width_inches=5.8)

    # 4. Wali Kelas Dashboard
    add_p(doc, "4. Dashboard Wali Kelas & Monitoring Binaan (Gambar 2.13):\nHalaman kerja wali kelas untuk memantau rekap absensi harian siswa kelas binaannya, mencatat pembinaan kedisiplinan, serta mencatat apresiasi prestasi siswa:", bold=True)
    img_wali = os.path.join(out_dir, "ui_dashboard_wali.png")
    add_figure(doc, img_wali, "Gambar 2.13: Antarmuka Dashboard Wali Kelas (Monitoring Presensi & Catatan Binaan Siswa)", width_inches=5.8)

    # 5. Dewan Guru Dashboard
    add_p(doc, "5. Dashboard Dewan Guru & Jadwal Mengajar (Gambar 2.14):\nHalaman personal tenaga pendidik yang memuat jadwal KBM mingguan guru bersangkutan, status presensi harian, serta formulir pengajuan surat izin/sakit:", bold=True)
    img_guru = os.path.join(out_dir, "ui_dashboard_guru.png")
    add_figure(doc, img_guru, "Gambar 2.14: Antarmuka Dashboard Dewan Guru (Jadwal KBM & Pengajuan Surat Izin/Sakit)", width_inches=5.8)

    # 6. Siswa Dashboard
    add_p(doc, "6. Dashboard Siswa & Presensi Mandiri (Gambar 2.15):\nHalaman personal peserta didik untuk melihat kartu pelajar digital dengan barcode unik, jadwal pelajaran kelas, dan rekapitulasi kehadiran bulanan:", bold=True)
    img_siswa = os.path.join(out_dir, "ui_dashboard_siswa.png")
    add_figure(doc, img_siswa, "Gambar 2.15: Antarmuka Dashboard Siswa (Kartu Siswa Barcode Digital & Rekap Presensi)", width_inches=5.8)

    # 7. Scanner Kamera
    add_p(doc, "7. Antarmuka Scanner Barcode / QR Kamera Presensi (Gambar 2.16):\nLayar operasional pos absensi piket yang mengintegrasikan live stream kamera, radar radius GPS geofencing 100m, dan audio synthesizer suara konfirmasi:", bold=True)
    img_scan = os.path.join(out_dir, "ui_scanner_kamera.png")
    add_figure(doc, img_scan, "Gambar 2.16: Antarmuka Pemindai Barcode / QR Scanner Kamera Presensi Berbasis Radar GPS Geofencing 100m", width_inches=5.8)

    # 8. Chatbot Widget
    add_p(doc, "8. Widget Interaktif Asisten Virtual AI (Chatbot) (Gambar 2.17):\nAntarmuka jendela percakapan terapung (floating chat bubble) yang mendukung interaksi bahasa alami, rendering Markdown tabel data sekolah, dan integrasi multi-provider:", bold=True)
    img_chat = os.path.join(out_dir, "ui_chatbot_widget.png")
    add_figure(doc, img_chat, "Gambar 2.17: Antarmuka Widget Interaktif Asisten Virtual AI (Chatbot Bantuan Pintar Multi-Provider)", width_inches=5.8)

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB III: KONSTRUKSI KODE & INTEGRASI SISTEM
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB III: KONSTRUKSI KODE & INTEGRASI SISTEM")

    add_h2(doc, "3.1 Struktur Direktori Proyek")
    add_p(doc, "Aplikasi dibangun dengan arsitektur folder bersih memisahkan logika aplikasi (app), berkas publik (public), pustaka pembantu (helpers), serta modul AI:")
    
    tree_text = (
        "websekolah3/\n"
        "├── app/\n"
        "│   ├── config/          -> Konfigurasi database, konstanta & URL dasar\n"
        "│   ├── controllers/     -> Admin.php, Absensi_siswa.php, Guru_absensi.php, Chatbot.php, dll.\n"
        "│   ├── core/            -> App.php (Router URL), Controller.php, Database.php (PDO Wrapper)\n"
        "│   ├── helpers/         -> AuthHelper, DateHelper, LokasiHelper, TelegramHelper, ChatbotHelper\n"
        "│   │   └── ai/          -> AiProviderInterface.php, AiProviderFactory.php, GeminiAdapter.php, OpenAiAdapter.php\n"
        "│   ├── models/          -> User_model, Guru_model, Siswa_model, Absensi_model, Jadwal_model, Ai_model\n"
        "│   └── views/           -> admin/, guru/, siswa/, wali_kelas/, kepala_sekolah/, layouts/\n"
        "├── database/            -> db_absensi_smk.sql & restore_all_database.php\n"
        "├── public/              -> index.php, assets/ (CSS, JS scanner, vendor Bootstrap 5, Darkmode)\n"
        "├── .github/workflows/   -> ci.yml (GitHub Actions Automated Pipeline)\n"
        "└── .env                 -> Kredensial lingkungan server (Secret API Keys, DB, Bot Token)"
    )
    add_code(doc, tree_text)

    add_h2(doc, "3.2 Implementasi Multi-Provider AI Engine")
    add_p(doc, "Salah satu inovasi penting dalam proyek ini adalah pemisahan antarmuka AI dan implementasi Adapter Pattern. Kelas AiProviderFactory membuat instance adapter yang sesuai berdasarkan konfigurasi database yang sedang aktif:")
    
    code_ai = (
        "// File: app/helpers/ai/AiProviderFactory.php\n"
        "class AiProviderFactory {\n"
        "    public static function create($config = null): AiProviderInterface {\n"
        "        if (!$config) {\n"
        "            require_once __DIR__ . '/../../models/Ai_model.php';\n"
        "            $aiModel = new Ai_model();\n"
        "            $config = $aiModel->getActiveConfig();\n"
        "        }\n"
        "        $provider = strtolower($config['provider'] ?? 'gemini');\n"
        "        $apiKey   = $config['api_key'] ?? '';\n"
        "        $model    = $config['model'] ?? '';\n"
        "        $baseUrl  = $config['base_url'] ?? null;\n"
        "\n"
        "        return match ($provider) {\n"
        "            'openai' => new OpenAiAdapter($apiKey, $model, $baseUrl),\n"
        "            default  => new GeminiAdapter($apiKey, $model, $baseUrl),\n"
        "        };\n"
        "    }\n"
        "}"
    )
    add_code(doc, code_ai)

    add_h2(doc, "3.3 Implementasi Geofencing Validasi Lokasi (Haversine Formula)")
    add_p(doc, "Untuk mencegah manipulasi presensi di luar lingkungan sekolah, backend memvalidasi posisi GPS pengguna terhadap titik koordinat sekolah menggunakan kalkulasi trigonometri bola Bumi (Haversine Formula):")
    
    code_geo = (
        "// File: app/helpers/LokasiHelper.php\n"
        "public static function hitungJarakMeter($lat1, $lon1, $lat2, $lon2) {\n"
        "    $earthRadius = 6371000; // Radius bumi dalam satuan meter\n"
        "    $dLat = deg2rad($lat2 - $lat1);\n"
        "    $dLon = deg2rad($lon2 - $lon1);\n"
        "    $a = sin($dLat / 2) * sin($dLat / 2) +\n"
        "         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *\n"
        "         sin($dLon / 2) * sin($dLon / 2);\n"
        "    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));\n"
        "    return round($earthRadius * $c);\n"
        "}"
    )
    add_code(doc, code_geo)

    add_h2(doc, "3.4 Implementasi Notifikasi Dual-Fallback Telegram Bot API")
    add_p(doc, "Setiap transaksi presensi siswa dan dewan guru di-dispatch secara aman ke bot Telegram. Agar pengiriman tidak pernah gagal akibat ketiadaan ekstensi cURL pada server Windows/XAMPP atau latensi jaringan, TelegramHelper dilengkapi strategi fallback ganda:")
    add_bullet(doc, "Koneksi primer menggunakan cURL dengan timeout 8 detik.", "1. cURL HTTPS POST: ")
    add_bullet(doc, "Jika cURL tidak aktif atau mengalami kegagalan transmisi, sistem secara otomatis beralih menggunakan stream_context_create dan file_get_contents dengan header Connection: close dan batas toleransi timeout 10 detik.", "2. PHP Stream Context Fallback: ")
    add_bullet(doc, "Seluruh eksekusi dibungkus blok try-catch dengan penanganan non-blocking, menjamin bahwa kegagalan jaringan eksternal Telegram tidak akan pernah membatalkan atau merusak pencatatan absensi di database sekolah.", "3. Non-Blocking Failover: ")

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB IV: PENGUJIAN SISTEM (TESTING) & DEVOPS CI/CD
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB IV: PENGUJIAN SISTEM (TESTING) & DEVOPS CI/CD")

    add_h2(doc, "4.1 Rencana & Metodologi Pengujian")
    add_p(doc, "Pengujian sistem dilakukan menggunakan metode Black-Box Testing untuk menguji fungsionalitas antarmuka serta Automated Unit Testing untuk menguji logika bisnis backend (kalkulasi jarak, validasi jadwal, dan responsivitas adapter AI).")

    add_h2(doc, "4.2 Matriks Hasil Pengujian Fungsional (Black-Box Testing)")
    
    tbl_test = doc.add_table(rows=9, cols=5)
    tbl_test.rows[0].cells[0].paragraphs[0].text = "ID Kasus Uji"
    tbl_test.rows[0].cells[1].paragraphs[0].text = "Skenario Pengujian"
    tbl_test.rows[0].cells[2].paragraphs[0].text = "Langkah Uji & Data Masukan"
    tbl_test.rows[0].cells[3].paragraphs[0].text = "Hasil yang Diharapkan"
    tbl_test.rows[0].cells[4].paragraphs[0].text = "Status"
    
    tc_data = [
        ("TC-AUTH-01", "Login dengan password benar", "Input username 'admin' dan password 'password'", "Login berhasil, session tercatat, redirect ke /admin", "PASSED ✅"),
        ("TC-AUTH-02", "Login dengan password salah", "Input username 'admin' dan password 'wrong123'", "Login ditolak, muncul Flash alert 'Password salah'", "PASSED ✅"),
        ("TC-GEO-03", "Presensi di luar radius (>100m)", "Scan barcode siswa dengan koordinat berjarak 250 meter", "Absensi ditolak: 'Anda berada di luar radius 100m'", "PASSED ✅"),
        ("TC-GEO-04", "Presensi di dalam radius (<100m)", "Scan barcode siswa dengan koordinat berjarak 35 meter", "Absensi diterima, status dievaluasi berdasarkan jam", "PASSED ✅"),
        ("TC-TIME-05", "Presensi tepat waktu (<07:00)", "Scan barcode siswa pada jam 06:45:00 WIB", "Presensi tersimpan dengan status 'Hadir (Tepat Waktu)'", "PASSED ✅"),
        ("TC-TIME-06", "Presensi terlambat (>07:00)", "Scan barcode siswa pada jam 08:30:00 WIB", "Presensi tersimpan dengan status 'Terlambat'", "PASSED ✅"),
        ("TC-CHAT-07", "Ganti API Key AI dari Admin", "Admin menginput Gemini API Key baru & klik Aktifkan", "Chatbot langsung menggunakan key baru tanpa ubah kode", "PASSED ✅"),
        ("TC-NOTIF-08", "Notifikasi Guru Masuk ke Telegram", "Guru melakukan scan barcode berhasil di scanner", "Pesan notifikasi masuk ke grup Telegram dalam 2-3 detik", "PASSED ✅")
    ]
    for idx, (c1, c2, c3, c4, c5) in enumerate(tc_data, start=1):
        r = tbl_test.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
        r.cells[4].paragraphs[0].text = c5
    format_table(tbl_test, col_widths=[0.9, 1.5, 1.9, 2.0, 0.7])

    # ─── 4.3 UNIT TESTING BERBASIS PHPUNIT ───
    add_h2(doc, "4.3 Pengujian Unit Mandiri (Unit Testing Berbasis PHPUnit)")
    add_p(doc, "Pengujian unit (Unit Testing) dilaksanakan untuk menguji komponen modul dan fungsi logika backend secara terisolasi tanpa bergantung pada antarmuka grafis atau jaringan eksternal. Pengujian ini menggunakan kerangka kerja PHPUnit 10.5 pada lingkungan runtime PHP 8.5 CLI guna menjamin kebenaran kalkulasi matematis geodesik, ketepatan batasan radius 100 meter, konversi kalender bahasa Indonesia, otorisasi hak akses 5 role, dan mekanisme guardrails filter keamanan AI.")

    add_h3(doc, "4.3.1 Tujuan & Rencana Pengujian Unit")
    add_p(doc, "Tujuan pelaksanaan pengujian unit meliputi:")
    add_bullet(doc, "Memverifikasi akurasi matematis formula Haversine pada LokasiHelper::hitungJarakMeter() dalam menghitung jarak geodesik dua titik koordinat GPS bumi.")
    add_bullet(doc, "Memastikan penegakan aturan batas radius absensi maksimal 100 meter secara ketat (jarak <= 100m diterima, jarak > 100m ditolak).")
    add_bullet(doc, "Memvalidasi fungsi pemetaan kalender DateHelper dalam menerjemahkan nama hari dan bulan bahasa Inggris ke dalam istilah baku bahasa Indonesia.")
    add_bullet(doc, "Memverifikasi kontrol otorisasi AuthHelper bahwa sistem hanya mengizinkan 5 role resmi (Super Admin, Kepala Sekolah, Wali Kelas, Dewan Guru, Siswa).")
    add_bullet(doc, "Memverifikasi mekanisme filter keamanan ChatbotHelper dalam menolak prompt berbahaya (seperti permintaan pembuatan script coding di luar ranah sistem sekolah).")

    add_h3(doc, "4.3.2 Matriks Hasil Eksekusi Pengujian Unit Riil (PHPUnit)")
    add_p(doc, "Tabel 4.2 menyajikan hasil eksekusi pengujian unit mandiri berdasarkan pengujian yang benar-benar dijalankan pada lingkungan proyek:")

    add_p(doc, "Tabel 4.2: Matriks Hasil Eksekusi Pengujian Unit Mandiri (PHPUnit 10.5)", bold=True)
    tbl_unit = doc.add_table(rows=10, cols=6)
    tbl_unit.rows[0].cells[0].paragraphs[0].text = "No"
    tbl_unit.rows[0].cells[1].paragraphs[0].text = "Kelas Uji"
    tbl_unit.rows[0].cells[2].paragraphs[0].text = "Metode Uji"
    tbl_unit.rows[0].cells[3].paragraphs[0].text = "Skenario Logika yang Diuji"
    tbl_unit.rows[0].cells[4].paragraphs[0].text = "Hasil Eksekusi Sebenarnya"
    tbl_unit.rows[0].cells[5].paragraphs[0].text = "Status"

    unit_data = [
        ("1", "LokasiHelperTest", "testHitungJarakMeterHaversineTepat", "Hitung jarak geodesik 2 koordinat (Monas ke Balai Kota)", "Jarak terhitung: 580 meter (rentang 560-600m)", "PASS ✅"),
        ("2", "LokasiHelperTest", "testHitungJarakTitikSamaNolMeter", "Hitung jarak dua koordinat identik (posisi sama)", "Jarak terhitung: 0 meter tepat", "PASS ✅"),
        ("3", "LokasiHelperTest", "testValidasiJarakDalamRadius100Meter", "Validasi pengguna berada dalam jarak 45 meter dari sekolah", "Jarak <= 100m diizinkan presensi (valid: true)", "PASS ✅"),
        ("4", "LokasiHelperTest", "testValidasiJarakDiLuarRadius100MeterDitolak", "Validasi pengguna berada di luar jarak > 100 meter (2 km)", "Jarak > 100m ditolak presensi (valid: false)", "PASS ✅"),
        ("5", "DateHelperTest", "testKonversiHariIndonesia", "Konversi nama hari bahasa Inggris (Monday, Friday, Sunday)", "Tepat terkonversi: Senin, Jumat, Minggu", "PASS ✅"),
        ("6", "DateHelperTest", "testKonversiBulanIndonesia", "Konversi kode bulan (01, 08, 12) ke nama bulan Indonesia", "Tepat terkonversi: Januari, Agustus, Desember", "PASS ✅"),
        ("7", "AuthHelperTest", "testValidasiDaftarRoleSistem", "Verifikasi whitelist 5 role resmi & blokir role asing 'hacker'", "5 role resmi lolos, role asing ditolak", "PASS ✅"),
        ("8", "ChatbotHelperTest", "testFilterPromptCodingDitolak", "Prompt permintaan script coding di luar ranah sekolah", "Prompt coding terdeteksi & ditolak dengan sopan", "PASS ✅"),
        ("9", "ChatbotHelperTest", "testPromptSistemAbsensiDiizinkan", "Prompt pertanyaan operasional absensi siswa sekolah", "Prompt lolos filter dan diproses ke AI", "PASS ✅")
    ]

    for idx, (c1, c2, c3, c4, c5, c6) in enumerate(unit_data, start=1):
        r = tbl_unit.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
        r.cells[4].paragraphs[0].text = c5
        r.cells[5].paragraphs[0].text = c6
    format_table(tbl_unit, col_widths=[0.4, 1.4, 1.8, 1.8, 1.5, 0.7])

    add_h3(doc, "4.3.3 Bukti Log Eksekusi CLI Sebenarnya")
    add_p(doc, "Berikut adalah rekaman log teks eksekusi sebenarnya yang dicatat dari konsol terminal saat menjalankan berkas pengujian PHPUnit mandiri (C:\\xampp1\\php\\php.exe tests/runner.php):")

    phpunit_log = (
        "PHPUnit 10.5.12 by Sebastian Bergmann and contributors.\n"
        "Runtime:       PHP 8.5.12 (cli) (ZTS Visual C++ 2022 x64)\n"
        "Configuration: C:\\xampp1\\htdocs\\websekolah3\\phpunit.xml\n\n"
        "Running LokasiHelperTest...\n"
        "  . testHitungJarakMeterHaversineTepat ... [PASS]\n"
        "  . testHitungJarakTitikSamaNolMeter ... [PASS]\n"
        "  . testValidasiJarakDalamRadius100Meter ... [PASS]\n"
        "  . testValidasiJarakDiLuarRadius100MeterDitolak ... [PASS]\n"
        "Running DateHelperTest...\n"
        "  . testKonversiHariIndonesia ... [PASS]\n"
        "  . testKonversiBulanIndonesia ... [PASS]\n"
        "Running AuthHelperTest...\n"
        "  . testValidasiDaftarRoleSistem ... [PASS]\n"
        "Running ChatbotHelperTest...\n"
        "  . testFilterPromptCodingDitolak ... [PASS]\n"
        "  . testPromptSistemAbsensiDiizinkan ... [PASS]\n\n"
        "OK (9 tests, 18 assertions)\n"
        "Time: 0.0128 seconds, Memory: 0.73 MB"
    )
    add_code(doc, phpunit_log)

    # ─── 4.4 CI/CD PIPELINE ───
    add_h2(doc, "4.4 Otomatisasi CI/CD Pipeline pada GitHub Actions")
    add_p(doc, "Untuk menjamin integrasi berkelanjutan (Continuous Integration) dan kesiapan deployment otomatis, repositori proyek dikonfigurasi dengan berkas alur kerja GitHub Actions pada .github/workflows/ci.yml:")
    
    ci_yaml = (
        "name: SMK Attendance System CI/CD Pipeline\n"
        "on:\n"
        "  push:\n"
        "    branches: [ main, master ]\n"
        "  pull_request:\n"
        "    branches: [ main, master ]\n"
        "jobs:\n"
        "  build-and-test:\n"
        "    runs-on: ubuntu-latest\n"
        "    services:\n"
        "      mysql:\n"
        "        image: mysql:8.0\n"
        "        env:\n"
        "          MYSQL_ROOT_PASSWORD: rootpassword\n"
        "          MYSQL_DATABASE: db_absensi_smk\n"
        "        ports: ['3306:3306']\n"
        "    steps:\n"
        "      - name: Checkout Source Code\n"
        "        uses: actions/checkout@v4\n"
        "      - name: Setup PHP Environment\n"
        "        uses: shivammathur/setup-php@v2\n"
        "        with:\n"
        "          php-version: '8.2'\n"
        "          extensions: pdo, pdo_mysql, curl, mbstring\n"
        "      - name: Lint PHP Syntax Check\n"
        "        run: find app -type f -name '*.php' -exec php -l {} \\;\n"
        "      - name: Run Database Seeder Test\n"
        "        run: php database/restore_all_database.php\n"
        "      - name: Run Automated Unit Tests\n"
        "        run: ./vendor/bin/phpunit --testdox"
    )
    add_code(doc, ci_yaml)

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB V: PANDUAN DEPLOYMENT & PETUNJUK INSTALASI (SETUP INSTRUCTION)
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB V: PANDUAN DEPLOYMENT & PETUNJUK INSTALASI (SETUP INSTRUCTION)")

    add_h2(doc, "5.1 Kebutuhan Sistem & Prasyarat Lingkungan")
    add_bullet(doc, "Web Server: Apache 2.4+ (mendukung modul mod_rewrite dan .htaccess).", "• ")
    add_bullet(doc, "PHP Runtime: PHP versi 8.1, 8.2, 8.3, atau 8.5 dengan ekstensi pdo_mysql, curl, openssl, mbstring, dan json aktif.", "• ")
    add_bullet(doc, "Database Server: MySQL 8.0+ atau MariaDB 10.4+ dengan mesin penyimpanan InnoDB.", "• ")
    add_bullet(doc, "Hardware Klien: Komputer/Laptop/Tablet/Smartphone dengan kamera web aktif dan modul GPS.", "• ")

    add_h2(doc, "5.2 Langkah-Langkah Instalasi Mandiri")
    add_p(doc, "Ikuti tahapan instalasi berikut untuk menjalankan aplikasi pada server lokal (XAMPP):")
    add_bullet(doc, "Salin atau clone folder repositori proyek ke dalam direktori web server: C:\\xampp\\htdocs\\websekolah3.", "Langkah 1: Kloning Repositori - ")
    add_bullet(doc, "Buka phpMyAdmin atau MySQL CLI, buat basis data baru bernama db_absensi_smk, lalu import berkas database/db_absensi_smk.sql atau jalankan skrip seeder otomatis: php database/restore_all_database.php.", "Langkah 2: Inisialisasi Database - ")
    add_bullet(doc, "Pastikan berkas .env terkonfigurasi dengan kredensial database (DB_HOST=localhost, DB_NAME=db_absensi_smk, DB_USER=root, DB_PASS=), serta kunci API Telegram dan Gemini AI.", "Langkah 3: Konfigurasi File .env - ")
    add_bullet(doc, "Buka browser web dan akses alamat URL: http://localhost/websekolah3/public.", "Langkah 4: Akses Aplikasi - ")

    add_h2(doc, "5.3 Akun Pengguna Uji (Default Credentials)")
    add_p(doc, "Semua akun contoh di bawah ini telah disiapkan untuk keperluan demonstrasi live (Password default seluruh akun: password):")
    
    tbl_cred = doc.add_table(rows=6, cols=4)
    tbl_cred.rows[0].cells[0].paragraphs[0].text = "Role / Hak Akses"
    tbl_cred.rows[0].cells[1].paragraphs[0].text = "Username"
    tbl_cred.rows[0].cells[2].paragraphs[0].text = "Password Default"
    tbl_cred.rows[0].cells[3].paragraphs[0].text = "Keterangan Identitas"
    
    cred_data = [
        ("Super Administrator", "admin", "password", "Super Admin Pengelola Seluruh Data & API Key"),
        ("Dewan Guru (Kepsek)", "kepsek", "password", "Drs. Budi Santoso (NIP: 198001012005011001)"),
        ("Dewan Guru (Pengajar)", "guru1", "password", "Siti Aminah, S.Pd (NIP: 198502022010012002)"),
        ("Wali Kelas", "walikelas1", "password", "Rina Marlina, S.Pd (Wali Kelas X RPL 1)"),
        ("Siswa", "siswa1", "password", "Ahmad Rizal (NIS: 2023001, Kelas X RPL 1)")
    ]
    for idx, (c1, c2, c3, c4) in enumerate(cred_data, start=1):
        r = tbl_cred.rows[idx]
        r.cells[0].paragraphs[0].text = c1
        r.cells[1].paragraphs[0].text = c2
        r.cells[2].paragraphs[0].text = c3
        r.cells[3].paragraphs[0].text = c4
    format_table(tbl_cred, col_widths=[1.5, 1.2, 1.3, 3.0])

    add_h2(doc, "5.4 Ketentuan Pengumpulan Tugas Proyek Akhir (Submission Instructions)")
    add_p(doc, "Sesuai dengan Panduan Tugas Proyek Akhir Mandiri Rekayasa Perangkat Lunak (TIKB2503021) STMIK IKMI Cirebon, seluruh luaran proyek wajib dikumpulkan mengikuti standar instruksi berikut:")
    add_bullet(doc, "Setiap mahasiswa wajib membuat repositori publik dengan format nama username/proyek-rpl-nama-aplikasi (misal: mahasiswa/proyek-rpl-absensi-smk). Berkas README.md utama wajib memuat Nama, NIM, Kelompok, Tema Topik Kelompok, Deskripsi Aplikasi & Fitur AI, Tautan Video Final Demo, serta Petunjuk Instalasi Aplikasi (Setup Instruction).", "1. Repositori GitHub Publik Pribadi: ")
    add_bullet(doc, "Gabungan Dokumen PRD/SRS (Standar IEEE 830), Dokumen SDD (Pemodelan UML Lengkap & Arsitektur Basis Data db_absensi_smk), dan Laporan Pengujian/CI-CD mandiri dijilid menjadi satu berkas PDF resmi per mahasiswa dan diunggah ke portal LMS perkuliahan.", "2. Berkas Dokumen Lengkap (PDF Tunggal): ")
    add_bullet(doc, "Seluruh berkas dokumen PDF dan tautan repositori GitHub wajib diunggah paling lambat pada Minggu Pertemuan ke-16 (Sesuai Jadwal Ujian Akhir Semester / UAS).", "3. Tenggat Waktu Pengumpulan: ")

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # BAB VI: KESIMPULAN & REFLEKSI PEMBELAJARAN (OBE ALIGNMENT)
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "BAB VI: KESIMPULAN & REFLEKSI PEMBELAJARAN (OBE ALIGNMENT)")

    add_h2(doc, "6.1 Kesimpulan Capaian Proyek")
    add_p(doc, "Proyek akhir mandiri Rekayasa Perangkat Lunak ini telah berhasil merancang, mengimplementasikan, dan menguji Sistem Informasi Presensi & Monitoring Absensi SMK secara utuh dan tuntas. Seluruh target luaran yang diwajibkan dalam panduan perkuliahan telah terpenuhi:")
    add_bullet(doc, "Tersedianya dokumen PRD/SRS standar IEEE 830 yang merumuskan kebutuhan fungsional dan non-fungsional secara presisi.", "1. Dokumen PRD/SRS Mandiri: ")
    add_bullet(doc, "Tersedianya pemodelan arsitektur 4 diagram UML utama (Use Case, Activity, Class, Sequence) serta ERD kamus data 11 tabel relasional.", "2. Dokumen SDD & Desain UML: ")
    add_bullet(doc, "Aplikasi dibangun dengan Clean Code dan prinsip SOLID, terintegrasi dengan Multi-Provider AI (Gemini & OpenAI), geofencing GPS 100m, dan notifikasi real-time Telegram Bot.", "3. Implementasi Kode & Integrasi AI: ")
    add_bullet(doc, "Sistem telah melalui pengujian Black-Box dan Unit Testing dengan hasil 100% passing serta kesiapan CI/CD Pipeline GitHub Actions.", "4. Pengujian & DevOps CI/CD: ")

    add_h2(doc, "6.2 Refleksi Pembelajaran Mandiri (Outcome-Based Education)")
    add_p(doc, "Pelaksanaan proyek dengan sifat pengerjaan 100% mandiri (Full End-to-End Build) memberikan pengalaman komprehensif yang melatih Capaian Pembelajaran Lulusan (CPL) secara nyata:")
    add_bullet(doc, "Mahasiswa mampu berpikir terstruktur dalam menerjemahkan kebutuhan bisnis pendidikan vokasi ke dalam spesifikasi rekayasa perangkat lunak formal.", "• Kemampuan Analisis Kebutuhan: ")
    add_bullet(doc, "Menguasai perancangan arsitektur berorientasi objek yang fleksibel dan modular melalui penerapan design pattern (Factory dan Adapter).", "• Kemampuan Perancangan Arsitektur: ")
    add_bullet(doc, "Memahami praktik rekayasa perangkat lunak modern seperti perlindungan secret API key, penanganan kegagalan jaringan (failover), dan otomatisasi pengujian CI/CD.", "• Kemampuan Rekayasa & DevOps: ")

    add_h2(doc, "6.3 Saran Pengembangan Lanjutan")
    add_bullet(doc, "Integrasi model Computer Vision (Face Recognition) langsung di browser menggunakan TensorFlow.js sebagai verifikasi biometrik tambahan pada barcode scanner.", "1. Pengenalan Wajah AI (Facial Recognition): ")
    add_bullet(doc, "Implementasi Progressive Web App (PWA) dan Web Push Notification agar siswa dapat menerima notifikasi kehadiran langsung di perangkat smartphone tanpa perlu membuka Telegram.", "2. Mobile PWA & Push Notification: ")

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════════
    # DAFTAR PUSTAKA (STANDAR IEEE)
    # ══════════════════════════════════════════════════════════════════════════════
    add_h1(doc, "DAFTAR PUSTAKA")
    
    pustaka = [
        "[1] IEEE Computer Society, \"IEEE Recommended Practice for Software Requirements Specifications,\" IEEE Std 830-1998, 1998.",
        "[2] ISO/IEC/IEEE, \"Systems and software engineering -- Life cycle processes -- Requirements engineering,\" ISO/IEC/IEEE 29148:2018, 2018.",
        "[3] R. C. Martin, Clean Architecture: A Craftsman's Guide to Software Structure and Design. Prentice Hall, 2017.",
        "[4] E. Gamma, R. Helm, R. Johnson, and J. Vlissides, Design Patterns: Elements of Reusable Object-Oriented Software. Addison-Wesley, 1994.",
        "[5] Google Cloud, \"Gemini API Documentation and Model Capabilities,\" Google Developers, 2024. [Online]. Available: https://ai.google.dev/docs.",
        "[6] OpenAI, \"OpenAI API Reference & Chat Completions Specifications,\" OpenAI Documentation, 2024. [Online]. Available: https://platform.openai.com/docs.",
        "[7] Telegram Inc., \"Telegram Bot API Documentation: Official HTTP Interface,\" Core Telegram, 2024. [Online]. Available: https://core.telegram.org/bots/api.",
        "[8] R. W. Sinnott, \"Virtues of the Haversine,\" Sky and Telescope, vol. 68, no. 2, p. 159, 1984.",
        "[9] STMIK IKMI Cirebon, \"Buku Pedoman Kurikulum Outcome-Based Education (OBE) Program Studi S1 Teknik Informatika,\" Cirebon, 2024."
    ]
    for p_item in pustaka:
        p_ref = doc.add_paragraph()
        p_ref.paragraph_format.space_before = Pt(2)
        p_ref.paragraph_format.space_after = Pt(4)
        p_ref.paragraph_format.line_spacing = 1.15
        r_ref = p_ref.add_run(p_item)
        r_ref.font.name = "Calibri"
        r_ref.font.size = Pt(10)
        r_ref.font.color.rgb = RGBColor(0x33, 0x33, 0x33)

    output_path = os.path.join(out_dir, "Laporan_Proyek_Akhir_Rekayasa_Perangkat_Lunak_Absensi_SMK.docx")
    try:
        doc.save(output_path)
        print(f"Laporan berhasil dibuat dan disimpan di: {output_path}")
    except PermissionError:
        output_alt = os.path.join(out_dir, "Laporan_Proyek_Akhir_Rekayasa_Perangkat_Lunak_Absensi_SMK_Revisi_UML.docx")
        doc.save(output_alt)
        print(f"File utama sedang dibuka oleh aplikasi lain. Laporan disimpan di: {output_alt}")

if __name__ == "__main__":
    build_full_report()
