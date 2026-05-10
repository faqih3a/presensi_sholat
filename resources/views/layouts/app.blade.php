<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Presensi Thursina | @yield('title')</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa; 
        }
        #wrapper { overflow-x: hidden; }
        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            margin-left: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #ffffff;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            border-right: 1px solid #edf2f9;
            display: flex;
            flex-direction: column;
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 1.5rem 1.25rem;
            font-size: 1.1rem;
            color: #333;
            background-color: #ffffff;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        #sidebar-wrapper .list-group { width: 100%; padding: 0.75rem; }
        #sidebar-wrapper .list-group-item {
            border: none;
            padding: 0.75rem 1rem;
            background-color: transparent;
            color: #67748e;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background-color 0.2s, color 0.2s, padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        #sidebar-wrapper .sidebar-text {
            transition: opacity 0.2s ease-in-out;
            opacity: 1;
            display: inline-block;
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        #sidebar-wrapper .list-group-item:hover {
            color: #333;
            background-color: #f8f9fa;
        }
        #sidebar-wrapper .list-group-item.active {
            color: #fff;
            background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        #page-content-wrapper { 
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding-left: 0;
        }
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -250px; }
        
        /* Sidebar Responsive Logic */
        @media (min-width: 768px) {
            #page-content-wrapper { padding-left: 250px; }
            body.sb-sidenav-toggled #page-content-wrapper { padding-left: 0; }
            
            /* Sidebar Mini (Desktop) */
            body.sb-mini #sidebar-wrapper { width: 85px; }
            body.sb-mini #page-content-wrapper { padding-left: 85px; }
            
            body.sb-mini #sidebar-wrapper .sidebar-text { 
                opacity: 0;
                width: 0;
                display: none;
            }
            body.sb-mini #sidebar-wrapper .sidebar-heading div { 
                display: none !important; 
            }
            body.sb-mini #sidebar-wrapper .sidebar-heading { 
                padding: 1rem 0; 
                min-height: 60px;
                display: flex;
                align-items: center;
                justify-content: center !important;
            }
            body.sb-mini #sidebar-wrapper .sidebar-heading .btn-link {
                position: static;
                margin: 0;
            }
            body.sb-mini #sidebar-wrapper .list-group-item { 
                justify-content: center; 
                padding: 0.8rem 0;
                margin: 0.4rem 0.8rem;
                min-height: 50px;
            }
            body.sb-mini #sidebar-wrapper .list-group-item i { 
                margin: 0; 
                font-size: 1.3rem;
            }
            body.sb-mini #sidebar-wrapper .list-group-item.active {
                background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
            }
            body.sb-mini #sidebar-wrapper .mt-auto {
                padding: 1rem 0 !important;
            }
        }
        @media (max-width: 767.98px) {
            #sidebar-wrapper { margin-left: -250px; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
            body.sb-sidenav-toggled #sidebar-overlay { display: block; }
        }
        
        #sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.3);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }

        .navbar-light .navbar-nav .nav-link { color: #67748e; font-weight: 500; }
        .navbar-light .navbar-nav .nav-link:hover { color: #333; }
        
        /* Remove Bootstrap Caret */
        .dropdown-toggle::after {
            display: none !important;
        }
        
        /* Page Transitions */
        /* Content Fade-in */
        #page-content-wrapper {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }
        body.loaded #page-content-wrapper {
            opacity: 1;
        }
        
        /* Dark Mode Styles */
        body.dark-mode { background-color: #121212; color: #e0e0e0; }
        body.dark-mode #sidebar-wrapper { background-color: #1e1e1e; border-right-color: #333; }
        body.dark-mode #sidebar-wrapper .sidebar-heading { background-color: #1e1e1e; color: #fff; border-bottom-color: #333 !important; }
        body.dark-mode #sidebar-wrapper .list-group-item { color: #adb5bd; }
        body.dark-mode #sidebar-wrapper .list-group-item:hover { color: #fff; background-color: #2c2c2c; }
        body.dark-mode .navbar { background-color: #1e1e1e !important; border-bottom-color: #333 !important; }
        body.dark-mode .navbar-light .navbar-nav .nav-link { color: #adb5bd; }
        body.dark-mode .navbar-light .navbar-nav .nav-link:hover { color: #fff; }
        body.dark-mode .card { background-color: #1e1e1e; border-color: #333; }
        body.dark-mode .card-header { background-color: #1e1e1e; border-bottom-color: #333; }
        body.dark-mode .text-gray-800, body.dark-mode .text-dark, body.dark-mode .text-primary { color: #f8f9fa !important; }
        body.dark-mode .table { color: #e0e0e0; border-color: #444; }
        body.dark-mode .table-light { background-color: #2c2c2c; color: #fff; border-color: #444; }
        body.dark-mode .table-hover tbody tr:hover { color: #fff; background-color: rgba(255,255,255,0.05); }
        body.dark-mode .dropdown-menu { background-color: #1e1e1e; border-color: #333; }
        body.dark-mode .dropdown-item { color: #e0e0e0; }
        body.dark-mode .dropdown-item:hover { background-color: #2c2c2c; color: #fff; }
        body.dark-mode .border-bottom { border-bottom-color: #333 !important; }
        body.dark-mode .border-top { border-top-color: #333 !important; }
        body.dark-mode .text-muted { color: #adb5bd !important; }
        body.dark-mode .bg-white { background-color: #1e1e1e !important; }
        body.dark-mode .page-loader { background-color: #121212; }
        
        /* Form Elements for Dark Mode */
        body.dark-mode .form-control, body.dark-mode .form-select {
            background-color: #2c2c2c;
            color: #f8f9fa;
            border-color: #444;
        }
        body.dark-mode .form-control:focus, body.dark-mode .form-select:focus {
            background-color: #2c2c2c;
            color: #fff;
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        body.dark-mode .form-control::placeholder { color: #6c757d; }
        body.dark-mode .input-group-text, body.dark-mode .btn-outline-secondary {
            background-color: #1e1e1e;
            color: #adb5bd;
            border-color: #444;
        }
        
        /* Modern Select Styles */
        .form-select {
            border: 1px solid #edf2f9;
            border-radius: 0.6rem;
            padding: 0.5rem 2.25rem 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #4d5157;
            transition: all 0.2s;
            cursor: pointer;
            background-color: #fff;
        }
        .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
            outline: 0;
        }
        
        /* Modern Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
            border-radius: 1rem !important;
            padding: 0.5rem;
            margin-top: 10px !important;
            animation: dropdownFade 0.2s ease-out;
        }
        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item {
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #67748e;
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            transform: translateX(5px);
        }
        .dropdown-item i {
            font-size: 1rem;
            margin-right: 0.75rem;
        }
        
        /* Badge Soft Styles */
        .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .badge-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .badge-soft-info {
            background-color: rgba(58, 176, 255, 0.1);
            color: #3ab0ff;
            border: 1px solid rgba(58, 176, 255, 0.2);
        }
        
        body.dark-mode {
            background-color: #1a1a1a;
            color: #e9ecef;
        }
        body.dark-mode .form-select {
            background-color: #2c2c2c;
            border-color: #444;
            color: #adb5bd;
        }
        body.dark-mode .dropdown-menu {
            background-color: #2c2c2c;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4) !important;
        }
        body.dark-mode .dropdown-item {
            color: #adb5bd;
        }
        body.dark-mode .dropdown-item:hover {
            background-color: rgba(25, 135, 84, 0.2);
            color: #2dc57b;
        }

        body.dark-mode .btn-outline-secondary:hover { background-color: #333; color: #fff; }
        body.dark-mode .modal-content { background-color: #1e1e1e; border-color: #333; }
        body.dark-mode .modal-header, body.dark-mode .modal-footer { border-color: #333; }
        body.dark-mode .close, body.dark-mode .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
    @stack('styles')
</head>
<body>
    <script>
        // Apply dark mode immediately to avoid flash
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
    <!-- Global Page Content -->
    <div class="d-flex" id="wrapper">
        <!-- Overlay -->
        <div id="sidebar-overlay"></div>

        <!-- Sidebar-->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success fs-4"></i>
                    <span class="sidebar-text">Presensi</span>
                </div>
                <button class="btn btn-link text-muted p-0 d-none d-md-block" id="miniSidebarToggle">
                    <i class="bi bi-chevron-left" id="miniSidebarIcon"></i>
                </button>
            </div>
            <div class="list-group list-group-flush flex-grow-1">
                @auth
                    @if(auth()->user()->role === 'super_admin')
                        <a class="list-group-item list-group-item-action {{ request()->is('super-admin/dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="sidebar-text">Dashboard Super Admin</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('super-admin/asatidz*') ? 'active' : '' }}" href="{{ route('asatidz.index') }}">
                            <i class="bi bi-person-workspace"></i>
                            <span class="sidebar-text">Kelola Asatidz</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('santri*') ? 'active' : '' }}" href="{{ route('santri.index') }}">
                            <i class="bi bi-people-fill"></i>
                            <span class="sidebar-text">Kelola Santri</span>
                        </a>
                    @endif
                    @if(auth()->user()->role === 'asatidz')
                        <a class="list-group-item list-group-item-action {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                            <i class="bi bi-grid-fill"></i>
                            <span class="sidebar-text">Dashboard Admin</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('santri') ? 'active' : '' }}" href="/santri">
                            <i class="bi bi-people-fill"></i>
                            <span class="sidebar-text">Data Santri</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('scan') ? 'active' : '' }}" href="/scan">
                            <i class="bi bi-camera-fill"></i>
                            <span class="sidebar-text">Presensi</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('kehadiran-sholat') ? 'active' : '' }}" href="/kehadiran-sholat">
                            <i class="bi bi-clipboard2-check-fill"></i>
                            <span class="sidebar-text">Kehadiran Sholat</span>
                        </a>
                    @endif
                    @if(auth()->user()->role === 'santri')
                        <a class="list-group-item list-group-item-action {{ request()->is('santri/dashboard') ? 'active' : '' }}" href="/santri/dashboard">
                            <i class="bi bi-person-vcard-fill"></i>
                            <span class="sidebar-text">Presensi Saya</span>
                        </a>
                        <a class="list-group-item list-group-item-action {{ request()->is('scan') ? 'active' : '' }}" href="/scan">
                            <i class="bi bi-qr-code-scan"></i>
                            <span class="sidebar-text">Scan Kehadiran</span>
                        </a>
                    @endif
                @endauth
            </div>
            
            <!-- Dark Mode Toggle -->
            <div class="mt-auto p-3 border-top text-center">
                <button class="btn btn-link text-secondary p-2 rounded-circle hover-bg-light" id="darkModeBtn" title="Toggle Dark Mode">
                    <i class="bi bi-moon-stars fs-5" id="darkModeIcon"></i>
                </button>
            </div>
        </div>

        
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-link text-muted p-0 me-2" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Search Bar -->
                        <form action="{{ route('dashboard.kehadiran') }}" method="GET" class="ms-md-3 me-auto d-none d-md-block no-loader" style="width: 250px;">
                            <div class="input-group input-group-sm bg-light rounded-3 border-0">
                                <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control bg-transparent border-0 ps-1" placeholder="Cari santri..." aria-label="Search" value="{{ request('search') }}">
                            </div>
                        </form>

                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                            @auth
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        @php
                                            $navAvatarUrl = null;
                                            if(auth()->user()->role === 'santri' && auth()->user()->santri && auth()->user()->santri->foto_referensi) {
                                                $navAvatarUrl = asset('storage/santri_fotos/' . auth()->user()->santri->foto_referensi);
                                            } elseif (auth()->user()->avatar) {
                                                $navAvatarUrl = asset('storage/avatars/' . auth()->user()->avatar);
                                            }
                                        @endphp
                                        
                                        @if($navAvatarUrl)
                                            <img src="{{ $navAvatarUrl }}" alt="Profile" class="rounded-circle shadow-sm" style="width: 35px; height: 35px; object-fit: cover; border: 2px solid #198754;">
                                        @else
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 35px; height: 35px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        @endif
                                        <span class="fw-semibold d-none d-sm-inline text-dark ms-1">{{ auth()->user()->name }}</span>
                                        <i class="bi bi-chevron-down small text-muted d-none d-sm-inline ms-1"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <div class="px-3 py-2">
                                            <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                            <div class="small text-muted">{{ auth()->user()->email }}</div>
                                        </div>
                                        <a class="dropdown-item {{ request()->is('profile') && !request()->has('tab') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                                            <i class="bi bi-person-circle text-success"></i> Profil Saya
                                        </a>
                                        <a class="dropdown-item {{ request('tab') === 'security' ? 'active' : '' }}" href="{{ route('profile.index', ['tab' => 'security']) }}">
                                            <i class="bi bi-shield-lock text-info"></i> Keamanan Akun
                                        </a>
                                        <div class="dropdown-divider mx-2"></div>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-box-arrow-right"></i> Keluar
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @else
                                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Page content-->
            <div class="container-fluid p-4 d-flex flex-column" style="min-height: calc(100vh - 56px);">
                <div class="flex-grow-1">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="d-flex flex-column flex-md-row justify-content-between align-items-center text-muted small mt-5 pt-4 border-top">
                    <div class="mb-3 mb-md-0">
                        © 2026, made with <i class="bi bi-heart-fill text-danger"></i> by <a href="#" class="text-success text-decoration-none fw-semibold">Ahmad Al-Faqih Asasi</a> for a better web. • Distributed by <a href="#" class="text-success text-decoration-none fw-semibold">Thursina</a>
                    </div>
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-muted text-decoration-none">Creative Tim</a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-muted text-decoration-none">License</a></li>
                    </ul>
                </footer>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark Mode Logic
        const darkModeBtn = document.getElementById('darkModeBtn');
        const darkModeIcon = document.getElementById('darkModeIcon');
        
        const updateDarkModeUI = (isDark) => {
            if (isDark) {
                document.body.classList.add('dark-mode');
                if (darkModeIcon) darkModeIcon.className = 'bi bi-sun fs-5 text-warning';
            } else {
                document.body.classList.remove('dark-mode');
                if (darkModeIcon) darkModeIcon.className = 'bi bi-moon-stars fs-5';
            }
        };

        // Initialize state based on the preload script
        updateDarkModeUI(document.body.classList.contains('dark-mode'));

        if (darkModeBtn) {
            darkModeBtn.addEventListener('click', () => {
                const isDark = document.body.classList.toggle('dark-mode');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateDarkModeUI(isDark);
            });
        }

        // Toggle the side navigation
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            const miniSidebarToggle = document.getElementById('miniSidebarToggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const miniSidebarIcon = document.getElementById('miniSidebarIcon');

            // Apply mini state from localStorage
            if (localStorage.getItem('sidebar-mini') === 'true') {
                document.body.classList.add('sb-mini');
                if (miniSidebarIcon) miniSidebarIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
            }

            const toggleSidebar = (e) => {
                if (e) e.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            };

            const toggleMiniSidebar = (e) => {
                if (e) e.preventDefault();
                document.body.classList.toggle('sb-mini');
                const isMini = document.body.classList.contains('sb-mini');
                localStorage.setItem('sidebar-mini', isMini);
                
                if (miniSidebarIcon) {
                    if (isMini) {
                        miniSidebarIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
                    } else {
                        miniSidebarIcon.classList.replace('bi-chevron-right', 'bi-chevron-left');
                    }
                }
            };

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', (e) => {
                    if (window.innerWidth >= 768) {
                        toggleMiniSidebar(e);
                    } else {
                        toggleSidebar(e);
                    }
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
            if (miniSidebarToggle) {
                miniSidebarToggle.addEventListener('click', toggleMiniSidebar);
            }
        });

        // Handle Content Fade-in
        window.addEventListener('load', function() {
            document.body.classList.add('loaded');
        });

        // Handle browser back button (bfcache)
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                document.body.classList.add('loaded');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
