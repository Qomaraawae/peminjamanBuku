<?php
// Require login untuk semua halaman kecuali login dan register
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php'];

if (!in_array($current_page, $public_pages)) {
    require_login();
}

// Get current user data
$current_user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Peminjaman Buku'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* User Info Dropdown Styles */
        .user-info {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 18px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: inherit;
            font-size: 15px;
        }

        .user-button:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .user-name {
            font-weight: 600;
        }

        .user-role {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dropdown-icon {
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .user-button.active .dropdown-icon {
            transform: rotate(180deg);
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            min-width: 250px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .dropdown-header strong {
            color: #333;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }

        .dropdown-header small {
            color: #999;
            font-size: 13px;
        }

        .dropdown-menu {
            padding: 10px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .dropdown-item.logout {
            color: #ff6b6b;
        }

        .dropdown-item.logout:hover {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-badge.admin {
            background: #d3f9d8;
            color: #2b8a3e;
        }

        .role-badge.user {
            background: #d0ebff;
            color: #1864ab;
        }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            .user-info {
                width: 100%;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .user-button {
                width: 100%;
                justify-content: space-between;
                padding: 18px 25px;
                border-radius: 0;
            }

            .user-dropdown {
                position: static;
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.05);
            }

            .dropdown-header {
                padding: 15px 25px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .dropdown-header strong,
            .dropdown-header small {
                color: white;
            }

            .dropdown-menu {
                padding: 5px 15px 15px;
            }

            .dropdown-item {
                color: white;
            }

            .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white;
            }

            .dropdown-item.logout:hover {
                background: rgba(255, 107, 107, 0.2);
                color: #ff6b6b;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Brand / Logo -->
            <div class="nav-brand">
                <div class="brand-icon">📚</div>
                <div class="brand-text">
                    <h2>Perpustakaan</h2>
                    <span class="brand-subtitle">Sistem Peminjaman</span>
                </div>
            </div>

            <!-- Hamburger Button -->
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <!-- Navigation Menu -->
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">🏠</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Menu untuk semua user -->
                <li class="nav-item">
                    <a href="buku.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'buku.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">📖</span>
                        <span class="nav-text">Data Buku</span>
                    </a>
                </li>

                <!-- Menu khusus Admin -->
                <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a href="anggota.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'anggota.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Data Anggota</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Menu untuk semua user -->
                <li class="nav-item">
                    <a href="peminjaman.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'peminjaman.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">📤</span>
                        <span class="nav-text">Peminjaman</span>
                    </a>
                </li>

                <!-- Menu Pengembalian - Hanya untuk Admin -->
                <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a href="pengembalian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pengembalian.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">📥</span>
                            <span class="nav-text">Pengembalian</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Laporan khusus Admin -->
                <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a href="laporan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Laporan</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- User Info & Logout -->
                <li class="nav-item user-info">

                    <!-- User Info & Logout -->
                <li class="nav-item user-info">
                    <button class="user-button" id="userButton">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar">👤</div>
                            <div style="text-align: left;">
                                <div class="user-name"><?php echo htmlspecialchars($current_user['nama_lengkap']); ?></div>
                                <div class="user-role"><?php echo htmlspecialchars($current_user['role']); ?></div>
                            </div>
                        </div>
                        <span class="dropdown-icon">▼</span>
                    </button>

                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <strong><?php echo htmlspecialchars($current_user['nama_lengkap']); ?></strong>
                            <small><?php echo htmlspecialchars($current_user['email']); ?></small>
                            <div style="margin-top: 8px;">
                                <span class="role-badge <?php echo $current_user['role']; ?>">
                                    <?php echo strtoupper($current_user['role']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item logout" onclick="showLogoutModal(); return false;">
                                <span>🚪</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="nav-overlay" id="navOverlay"></div>

    <!-- Custom Logout Modal -->
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <div class="logout-icon">
                <div class="logout-icon-circle">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <h2 class="logout-title">Keluar dari Akun?</h2>
            <p class="logout-message">Apakah Anda yakin ingin logout dari sistem?</p>
            <div class="logout-buttons">
                <button onclick="closeLogoutModal()" class="logout-btn logout-btn-cancel">
                    <span>Batal</span>
                </button>
                <button onclick="confirmLogout()" class="logout-btn logout-btn-confirm">
                    <span>Ya, Logout</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Hamburger Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const navMenu = document.getElementById('navMenu');
            const navOverlay = document.getElementById('navOverlay');
            const userButton = document.getElementById('userButton');
            const userDropdown = document.getElementById('userDropdown');
            const body = document.body;

            // Toggle menu
            hamburger.addEventListener('click', function(e) {
                e.stopPropagation();
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                navOverlay.classList.toggle('active');
                body.classList.toggle('menu-open');
            });

            // Close menu when clicking overlay
            navOverlay.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                navOverlay.classList.remove('active');
                body.classList.remove('menu-open');
                userButton.classList.remove('active');
                userDropdown.classList.remove('active');
            });

            // Close menu when clicking on a link (except user button)
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    navOverlay.classList.remove('active');
                    body.classList.remove('menu-open');
                });
            });

            // Toggle user dropdown
            userButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userButton.classList.toggle('active');
                userDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userButton.classList.remove('active');
                    userDropdown.classList.remove('active');
                }
            });

            // Close menu on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (navMenu.classList.contains('active')) {
                        hamburger.classList.remove('active');
                        navMenu.classList.remove('active');
                        navOverlay.classList.remove('active');
                        body.classList.remove('menu-open');
                    }
                    userButton.classList.remove('active');
                    userDropdown.classList.remove('active');
                    closeLogoutModal();
                }
            });
        });

        // Logout Modal Functions
        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('logoutModal').classList.add('active');
            }, 10);
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
            setTimeout(() => {
                document.getElementById('logoutModal').style.display = 'none';
            }, 300);
        }

        function confirmLogout() {
            window.location.href = 'logout.php';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('logoutModal');
            if (e.target === modal) {
                closeLogoutModal();
            }
        });
    </script>

    <style>
        /* Logout Modal Styles */
        .logout-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .logout-modal.active {
            opacity: 1;
        }

        .logout-modal.active .logout-modal-content {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .logout-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 440px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(-20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .logout-icon {
            margin-bottom: 25px;
        }

        .logout-icon-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
        }

        .logout-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
            margin-top: 0;
        }

        .logout-message {
            font-size: 16px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .logout-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .logout-btn {
            flex: 1;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .logout-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .logout-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .logout-btn span {
            position: relative;
            z-index: 1;
        }

        .logout-btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
        }

        .logout-btn-cancel:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .logout-btn-confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .logout-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        /* Mobile Responsive */
        @media (max-width: 500px) {
            .logout-modal-content {
                padding: 30px 25px;
            }

            .logout-icon-circle {
                width: 80px;
                height: 80px;
            }

            .logout-icon-circle svg {
                width: 45px;
                height: 45px;
            }

            .logout-title {
                font-size: 24px;
            }

            .logout-message {
                font-size: 14px;
            }

            .logout-buttons {
                flex-direction: column;
            }

            .logout-btn {
                width: 100%;
            }
        }
    </style>

    <div class="container main-content">

        <?php
        // Tampilkan pesan sukses jika ada
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }

        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }

        // Tampilkan pesan info jika ada
        if (isset($_SESSION['info'])) {
            echo '<div class="alert alert-info">' . $_SESSION['info'] . '</div>';
            unset($_SESSION['info']);
        }
        ?>