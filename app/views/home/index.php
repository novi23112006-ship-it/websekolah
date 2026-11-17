<!-- ===== HERO SECTION MODERN ===== -->
<style>
    .hero-section {
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        padding: 80px 0;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 300px; height: 300px;
        background: linear-gradient(135deg, rgba(78,115,223,0.1), rgba(34,74,190,0.1));
        border-radius: 50%;
        filter: blur(40px);
        z-index: 0;
    }
    .hero-content {
        position: relative;
        z-index: 1;
    }
    .hero-title {
        font-weight: 800;
        font-size: 3.5rem;
        background: linear-gradient(135deg, #224abe, #4e73df);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 20px;
        letter-spacing: -1px;
    }
    .feature-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 16px;
        padding: 30px 20px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(78,115,223,0.15);
    }
    .feature-icon {
        font-size: 40px;
        color: #4e73df;
        margin-bottom: 15px;
    }
    .btn-get-started {
        background: linear-gradient(135deg, #4e73df, #224abe);
        color: white;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 15px rgba(78,115,223,0.4);
        transition: all 0.3s ease;
    }
    .btn-get-started:hover {
        transform: scale(1.05);
        color: white;
        box-shadow: 0 8px 25px rgba(78,115,223,0.5);
    }
</style>

<div class="container mt-4">
    <div class="hero-section px-5">
        <div class="row align-items-center hero-content">
            <div class="col-lg-7">
                <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill shadow-sm" style="font-size: 14px;">🚀 Sistem Absensi Generasi Baru</span>
                <h1 class="hero-title">Sistem Absensi Digital SMK</h1>
                <p class="fs-5 text-secondary mb-4" style="line-height: 1.6;">Tinggalkan cara lama! Nikmati kemudahan mencatat kehadiran dengan teknologi <strong>Pemindaian Barcode/QR</strong> yang cepat dan akurat, didukung penuh oleh Asisten AI Pintar 24/7 untuk segala kebutuhan informasi Anda.</p>
                <a href="<?= BASEURL; ?>/auth" class="btn btn-get-started btn-lg text-decoration-none">Mulai Sekarang <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <!-- Animasi gambar sekolah -->
                <img src="<?= BASEURL; ?>/img/sekolah_ilustrasi.jpg" alt="Ilustrasi Sekolah" class="img-fluid rounded-4 shadow-lg border border-3 border-white" style="animation: float 6s ease-in-out infinite;">
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="feature-card">
                <i class="bi bi-qr-code-scan feature-icon"></i>
                <h4 class="fw-bold">Absensi Cepat & Akurat</h4>
                <p class="text-muted">Cukup scan Barcode/QR Code, kehadiran langsung tercatat otomatis dan real-time ke dalam sistem pusat sekolah.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="bi bi-display feature-icon"></i>
                <h4 class="fw-bold">Dashboard Real-time</h4>
                <p class="text-muted">Pantau rekapitulasi data kehadiran, jadwal, dan nilai secara langsung melalui dashboard intuitif.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="bi bi-robot feature-icon"></i>
                <h4 class="fw-bold">Asisten AI Cerdas</h4>
                <p class="text-muted">Bingung cara menggunakan sistem? Chatbot AI kami siap membantu Anda 24 jam non-stop dengan respon cepat.</p>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}
</style>

