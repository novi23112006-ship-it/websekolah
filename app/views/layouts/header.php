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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #F5F7FB;">
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center text-dark" href="<?= BASEURL; ?>/auth/login">
                <img src="<?= BASEURL; ?>/img/logo_sekolah.png" alt="Logo SMK" class="me-2" style="width: 34px; height: 34px; object-fit: contain;">
                <span style="font-size: 16px; font-weight: 700; color: #1F2937;">Sistem Absensi SMK</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-lock me-1"></i>Portal Akses
                </span>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
