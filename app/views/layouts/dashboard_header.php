<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul']; ?> - Sistem Absensi SMK</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Light Minimalist Design System -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/style.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        (function() {
            var theme = localStorage.getItem('app_theme') || localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <div class="app-layout" id="appLayout">
        <!-- Backdrop Overlay untuk Mobile Sidebar Drawer -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- ==========================================
             SIDEBAR (WHITE MINIMALIST)
             ========================================== -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Sidebar Brand Header -->
            <div class="sidebar-brand-wrapper">
                <button type="button" class="btn-sidebar-toggle" id="btnToggleSidebar" aria-label="Toggle Sidebar" title="Buka/Tutup Navigasi">
                    <i class="bi bi-list"></i>
                </button>
                <a href="<?= BASEURL; ?>/<?= $_SESSION['role'] ?? 'admin' ?>" class="sidebar-brand-link" title="Sistem Absensi SMK">
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-title">Sistem Absensi</span>
                        <span class="sidebar-brand-subtitle">SMK Negeri</span>
                    </div>
                </a>
                <button type="button" class="btn btn-sm btn-light border d-lg-none p-1 text-secondary ms-auto" id="btnSidebarClose" aria-label="Tutup Menu">
                    <i class="bi bi-x fs-5"></i>
                </button>
            </div>

            <!-- Navigation Scroll Area -->
            <div class="sidebar-nav-container">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-heading">Menu Utama</li>

                    <?php if($_SESSION['role'] == 'wali_kelas'): ?>
                        <!-- MENU KHUSUS WALI KELAS -->
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'dashboard') ? 'active' : '' ?>" href="<?= BASEURL; ?>/wali_kelas">
                                <i class="bi bi-speedometer2"></i>
                                <span class="sidebar-nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'data_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/wali_kelas/siswa">
                                <i class="bi bi-people"></i>
                                <span class="sidebar-nav-text">Data Siswa</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'absensi_kelas') ? 'active' : '' ?>" href="<?= BASEURL; ?>/wali_kelas/absensi">
                                <i class="bi bi-clipboard-check"></i>
                                <span class="sidebar-nav-text">Absensi Kelas</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'catatan_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/wali-kelas/catatan-siswa">
                                <i class="bi bi-journal-text"></i>
                                <span class="sidebar-nav-text">Catatan Siswa</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'rekap_absensi') ? 'active' : '' ?>" href="<?= BASEURL; ?>/wali_kelas/rekap">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                <span class="sidebar-nav-text">Rekap Absensi</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'laporan_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan/siswa">
                                <i class="bi bi-clock-history"></i>
                                <span class="sidebar-nav-text">Riwayat Absen Siswa</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'laporan_guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan/guru">
                                <i class="bi bi-person-check"></i>
                                <span class="sidebar-nav-text">Riwayat Absen Saya</span>
                            </a>
                        </li>

                    <?php elseif($_SESSION['role'] == 'kepala_sekolah'): ?>
                        <!-- MENU KHUSUS KEPALA SEKOLAH -->
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'dashboard') ? 'active' : '' ?>" href="<?= BASEURL; ?>/kepala_sekolah">
                                <i class="bi bi-speedometer2"></i>
                                <span class="sidebar-nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'data_guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/kepala_sekolah/guru">
                                <i class="bi bi-person-badge"></i>
                                <span class="sidebar-nav-text">Data Guru</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'rekap_guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/kepala_sekolah/rekap_guru">
                                <i class="bi bi-journal-check"></i>
                                <span class="sidebar-nav-text">Rekap Absensi Guru</span>
                            </a>
                        </li>

                    <?php else: ?>
                        <!-- MENU UMUM & ROLE LAIN -->
                        <li class="sidebar-nav-item">
                            <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'dashboard') ? 'active' : '' ?>" href="<?= BASEURL; ?>/<?= $_SESSION['role'] ?>">
                                <i class="bi bi-speedometer2"></i>
                                <span class="sidebar-nav-text">Dashboard</span>
                            </a>
                        </li>

                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <li class="sidebar-nav-heading">Master Data</li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'kelas') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_kelas">
                                    <i class="bi bi-diagram-3"></i>
                                    <span class="sidebar-nav-text">Data Kelas</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_guru">
                                    <i class="bi bi-people"></i>
                                    <span class="sidebar-nav-text">Data Guru</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_siswa">
                                    <i class="bi bi-mortarboard"></i>
                                    <span class="sidebar-nav-text">Data Siswa</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'mapel') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_mapel">
                                    <i class="bi bi-journal-bookmark"></i>
                                    <span class="sidebar-nav-text">Mata Pelajaran</span>
                                </a>
                            </li>

                            <li class="sidebar-nav-heading">Jadwal & Rekap</li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'jadwal_mengajar') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_jadwal_mengajar">
                                    <i class="bi bi-calendar3-range"></i>
                                    <span class="sidebar-nav-text">Jadwal Mengajar</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'jadwal') ? 'active' : '' ?>" href="<?= BASEURL; ?>/admin_jadwal">
                                    <i class="bi bi-calendar-week"></i>
                                    <span class="sidebar-nav-text">Jadwal Absensi</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && in_array($data['active_menu'], ['rekap_pusat', 'laporan', 'laporan_siswa', 'laporan_guru'])) ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                    <span class="sidebar-nav-text">Rekap Absensi</span>
                                </a>
                            </li>

                            <li class="sidebar-nav-heading">Terminal Scanner</li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'absensi_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/absensi_siswa" target="_blank" rel="noopener noreferrer" title="Buka Scanner Siswa di tab baru">
                                    <i class="bi bi-qr-code-scan"></i>
                                    <span class="sidebar-nav-text">Absensi Siswa</span>
                                    <i class="bi bi-box-arrow-up-right ms-auto small opacity-50" style="font-size: 11px;"></i>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'absensi_guru_scanner') ? 'active' : '' ?>" href="<?= BASEURL; ?>/guru_absensi" target="_blank" rel="noopener noreferrer" title="Buka Scanner Guru di tab baru">
                                    <i class="bi bi-person-badge"></i>
                                    <span class="sidebar-nav-text">Absensi Guru</span>
                                    <i class="bi bi-box-arrow-up-right ms-auto small opacity-50" style="font-size: 11px;"></i>
                                </a>
                            </li>

                        <?php elseif($_SESSION['role'] == 'guru'): ?>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'absensi_guru_scanner') ? 'active' : '' ?>" href="<?= BASEURL; ?>/guru_absensi">
                                    <i class="bi bi-person-badge"></i>
                                    <span class="sidebar-nav-text">Absensi Guru</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'jadwal_mengajar_guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/guru/jadwal">
                                    <i class="bi bi-calendar3-range"></i>
                                    <span class="sidebar-nav-text">Jadwal Mengajar</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'laporan_guru') ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan/guru">
                                    <i class="bi bi-clock-history"></i>
                                    <span class="sidebar-nav-text">Riwayat Absen</span>
                                </a>
                            </li>

                        <?php elseif($_SESSION['role'] == 'siswa'): ?>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'jadwal_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/siswa/jadwal">
                                    <i class="bi bi-calendar-week"></i>
                                    <span class="sidebar-nav-text">Jadwal Pelajaran</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-item">
                                <a class="sidebar-nav-link <?= (isset($data['active_menu']) && $data['active_menu'] == 'laporan_siswa') ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan/siswa">
                                    <i class="bi bi-clock-history"></i>
                                    <span class="sidebar-nav-text">Riwayat Absen</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Logout Link -->
                    <li class="sidebar-nav-item mt-4 pt-3 border-top">
                        <a class="sidebar-nav-link nav-logout" href="<?= BASEURL; ?>/auth/logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="sidebar-nav-text">Keluar (Logout)</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- ==========================================
             MAIN WRAPPER & TOPBAR
             ========================================== -->
        <div class="app-main-wrapper" id="appMainWrapper">
            <!-- Topbar Header -->
            <header class="app-topbar">
                <div class="topbar-left">
                    <button type="button" class="btn-sidebar-toggle d-lg-none" id="btnToggleSidebarMobile" aria-label="Toggle Sidebar" title="Buka/Tutup Navigasi">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASEURL; ?>/<?= $_SESSION['role'] ?? 'admin' ?>" class="topbar-brand-link d-inline-flex align-items-center text-decoration-none" title="Sistem Absensi SMK">
                            <img src="<?= BASEURL; ?>/img/logo_sekolah.png" alt="Logo SMK" class="topbar-logo-img" style="width: 32px; height: 32px; object-fit: contain;">
                        </a>
                        <h1 class="topbar-page-title mb-0"><?= $data['judul']; ?></h1>
                        <span class="topbar-page-badge d-none d-sm-inline-block">
                            <?= ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'user')); ?>
                        </span>
                    </div>
                </div>

                <div class="topbar-right">
                    <?php
                        $roleLink = ($_SESSION['role'] === 'wali_kelas' || $_SESSION['role'] === 'kepala_sekolah') ? $_SESSION['role'] : ($_SESSION['role'] === 'siswa' ? 'siswa' : ($_SESSION['role'] === 'guru' ? 'guru' : 'admin'));
                        $fotoPath = !empty($_SESSION['foto']) ? BASEURL . '/uploads/profil/' . htmlspecialchars($_SESSION['foto']) : null;
                    ?>
                    <div class="dropdown">
                        <!-- User Profile Pill Button -->
                        <button class="user-menu-pill dropdown-toggle" 
                                type="button" 
                                id="dropdownUserMenu" 
                                data-bs-toggle="dropdown" 
                                data-bs-auto-close="true"
                                aria-expanded="false">
                            <?php if ($fotoPath): ?>
                                <img src="<?= $fotoPath; ?>" alt="Foto" class="user-avatar-circle border">
                            <?php else: ?>
                                <div class="user-avatar-circle">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                            <div class="user-info-text d-none d-sm-block">
                                <span class="user-info-name"><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']); ?></span>
                                <span class="user-info-role"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></span>
                            </div>
                            <i class="bi bi-chevron-down text-muted small ms-1"></i>
                        </button>

                        <!-- User Profile Dropdown Menu -->
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border p-2 mt-2" aria-labelledby="dropdownUserMenu" style="min-width: 270px;">
                            <!-- Header Profil Pengguna -->
                            <li class="px-3 py-2 mb-2 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($fotoPath): ?>
                                        <img src="<?= $fotoPath; ?>" alt="Foto" class="rounded-circle border border-2 border-primary" style="width: 42px; height: 42px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 13.5px;"><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']); ?></h6>
                                        <span class="badge bg-primary mt-1" style="font-size: 10px;">
                                            <?= ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </li>

                            <!-- Item 1: Mode Terang / Gelap -->
                            <li>
                                <div class="dropdown-item d-flex align-items-center justify-content-between rounded-3 py-2 px-3 user-select-none" id="globalThemeToggle" style="cursor: pointer;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-moon-stars text-primary fs-5" id="themeIcon"></i>
                                        <div>
                                            <span class="fw-semibold text-dark d-block" style="font-size: 13px;">Mode Tampilan</span>
                                            <span class="text-muted small" style="font-size: 11px;">Ganti Terang / Gelap</span>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill border px-2 py-1 small fw-semibold" id="themeTextBadge" style="font-size: 11px;">
                                        <span id="themeText">Terang</span>
                                    </span>
                                </div>
                            </li>

                            <!-- Item 2: Pengaturan Akun / Profil -->
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 rounded-3 py-2 px-3 text-dark fw-semibold" href="<?= BASEURL; ?>/admin/pengaturan" style="font-size: 13px;">
                                    <i class="bi bi-gear text-primary fs-5"></i>
                                    <div>
                                        <span class="d-block">Pengaturan Akun</span>
                                        <span class="text-muted small fw-normal" style="font-size: 11px;">Password, foto & konfigurasi</span>
                                    </div>
                                </a>
                            </li>
                            <?php else: ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 rounded-3 py-2 px-3 text-dark fw-semibold" href="<?= BASEURL; ?>/<?= $roleLink; ?>/profil" style="font-size: 13px;">
                                    <i class="bi bi-person-circle text-primary fs-5"></i>
                                    <div>
                                        <span class="d-block">Profil Saya</span>
                                        <span class="text-muted small fw-normal" style="font-size: 11px;">Detail akun & foto profil</span>
                                    </div>
                                </a>
                            </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider my-2"></li>

                            <!-- Item 3: Logout -->
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 rounded-3 py-2 px-3 text-danger fw-semibold" href="<?= BASEURL; ?>/auth/logout" style="font-size: 13px;">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                    <span>Keluar (Logout)</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Starts Here -->
            <main class="app-content-body">
