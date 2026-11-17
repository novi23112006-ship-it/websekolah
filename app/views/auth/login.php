<style>
/* Container & Background styling */
.login-page-wrapper {
    min-height: calc(85vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}

/* Main Card - Compact & Clean */
.login-card-container {
    width: 100%;
    max-width: 440px;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.1), 0 4px 12px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(226, 232, 240, 0.85);
    padding: 36px 32px;
}

/* Brand Logo Header */
.login-brand-logo {
    width: 88px;
    height: 88px;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.12));
    transition: transform 0.3s ease;
}

.login-brand-logo:hover {
    transform: scale(1.05);
}

.signin-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 2px;
}

.signin-subtitle {
    font-size: 0.78rem;
    color: #94a3b8;
    margin-bottom: 20px;
}

/* Inputs styling (Preserving exact username & password fields) */
.custom-input-group .input-group-text {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    color: #64748b;
    padding: 8px 12px;
    border-radius: 10px 0 0 10px;
}

.custom-input-group .form-control {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    font-size: 0.88rem;
    padding: 9px 12px;
    color: #1e293b;
}

.custom-input-group .form-control:focus {
    background-color: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.custom-input-group .btn-toggle-pwd {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    border-radius: 0 10px 10px 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #1d4ed8;
    padding: 0 12px;
    transition: all 0.2s ease;
}

.custom-input-group .btn-toggle-pwd:hover {
    background-color: #f1f5f9;
    color: #1e40af;
}

.custom-input-group .input-group-text + .form-control {
    border-radius: 0;
}

/* Sign in Button */
.btn-signin-primary {
    background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
    border: none;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.92rem;
    padding: 10px 18px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(30, 64, 175, 0.22);
    transition: all 0.25s ease;
}

.btn-signin-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(30, 64, 175, 0.3);
    color: #ffffff;
}

/* Secondary Button ("Sign in with other" style) */
.btn-signin-secondary {
    background-color: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 9px 16px;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.btn-signin-secondary:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
    color: #1e293b;
}

/* Divider Or */
.divider-or {
    position: relative;
    text-align: center;
    margin: 14px 0;
}

.divider-or hr {
    border-top: 1px solid #e2e8f0;
    margin: 0;
    opacity: 1;
}

.divider-or span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #ffffff;
    padding: 0 10px;
    font-size: 0.72rem;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
    .login-card-container {
        padding: 28px 20px;
        border-radius: 20px;
    }
    .login-brand-logo {
        width: 72px;
        height: 72px;
    }
    .signin-title {
        font-size: 1.45rem;
    }
}
</style>

<div class="login-page-wrapper">
    <div class="login-card-container">
        <!-- Brand Header: Logo Sekolah & Identitas SMK NU Wargabinangun -->
        <div class="text-center mb-3">
            <img src="<?= BASEURL; ?>/img/logo_sekolah.png" alt="Logo SMK NU Wargabinangun" class="login-brand-logo mb-2">
            <h5 class="fw-bold text-dark mb-0" style="letter-spacing: 0.5px; font-size: 15px;">SMK NU WARGABINANGUN</h5>
            <p class="text-muted small mb-0" style="font-size: 11px;">KALIWEDI - CIREBON</p>
        </div>

        <div class="text-center mb-3">
            <h3 class="signin-title">Sign in</h3>
            <p class="signin-subtitle">Silakan masukkan nama lengkap atau username dan password Anda</p>
        </div>

        <!-- Alert Flash Message -->
        <?php Flasher::flash(); ?>

        <form action="<?= BASEURL; ?>/auth/process" method="POST" id="formLogin">
            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken(); ?>">

            <!-- Input Username / Nama Lengkap (Tampilan & Fungsi Tetap) -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-muted mb-1" style="font-size: 12px;">
                    Nama Lengkap / Username
                </label>
                <div class="input-group custom-input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="User Name / Nama Lengkap" required autofocus autocomplete="username">
                </div>
            </div>

            <!-- Input Password (Tampilan & Fungsi Tetap) -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-muted mb-1" style="font-size: 12px;">
                    Password
                </label>
                <div class="input-group custom-input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control border-end-0" id="password" name="password" 
                           placeholder="Password" required autocomplete="current-password">
                    <button class="btn btn-toggle-pwd" type="button" id="btnTogglePassword" title="Lihat/Sembunyikan Password">
                        <i class="bi bi-eye-slash me-1" id="eyeIcon"></i><span id="toggleText">HIDE</span>
                    </button>
                </div>
            </div>

            <!-- Row Remember Me & Info Keamanan -->
            <div class="d-flex justify-content-between align-items-center mb-3 pt-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label text-muted user-select-none" for="rememberMe" style="font-size: 12px;">
                        Remember me
                    </label>
                </div>
                <span class="text-muted" style="font-size: 11px;">
                    <i class="bi bi-shield-check text-success me-1"></i>Kredensial Aman
                </span>
            </div>

            <!-- Tombol Sign In Utama -->
            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-signin-primary d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Sign in</span>
                </button>
            </div>

            <!-- Divider Or -->
            <div class="divider-or">
                <hr>
                <span>Or</span>
            </div>

            <!-- Tombol Sign in with other / Tanya Bantuan AI -->
            <div class="d-grid mb-3">
                <button type="button" class="btn btn-signin-secondary d-flex align-items-center justify-content-center gap-2" id="btnOpenChatbotHelp">
                    <i class="bi bi-robot text-primary"></i>
                    <span>Butuh Bantuan? Tanya AI</span>
                </button>
            </div>

            <!-- Catatan Login -->
            <div class="text-center pt-1">
                <small class="text-muted" style="font-size: 11px;">
                    <i class="bi bi-info-circle me-1 text-primary"></i>Siswa & Guru dapat login menggunakan <strong>Nama Lengkap masing-masing</strong>
                </small>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnToggle = document.getElementById('btnTogglePassword');
    const pwdInput  = document.getElementById('password');
    const eyeIcon   = document.getElementById('eyeIcon');
    const toggleText = document.getElementById('toggleText');
    const btnAiHelp = document.getElementById('btnOpenChatbotHelp');
    
    // Toggle Password Visibility (Saat kelihatan = SHOW, saat tersembunyi = HIDE)
    if (btnToggle && pwdInput) {
        btnToggle.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                if (eyeIcon) {
                    eyeIcon.classList.remove('bi-eye-slash');
                    eyeIcon.classList.add('bi-eye');
                }
                if (toggleText) toggleText.textContent = 'SHOW';
            } else {
                pwdInput.type = 'password';
                if (eyeIcon) {
                    eyeIcon.classList.remove('bi-eye');
                    eyeIcon.classList.add('bi-eye-slash');
                }
                if (toggleText) toggleText.textContent = 'HIDE';
            }
        });
    }

    // Sambungkan tombol Bantuan AI ke Chatbot Panel jika ada
    if (btnAiHelp) {
        btnAiHelp.addEventListener('click', function() {
            const chatbotToggle = document.getElementById('chatbot-toggle');
            if (chatbotToggle) {
                chatbotToggle.click();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Bantuan Akun',
                    text: 'Silakan gunakan Nama Lengkap (contoh: Ahmad Fauzi) atau Username Anda dengan password yang telah diberikan sekolah.'
                });
            }
        });
    }
});
</script>
