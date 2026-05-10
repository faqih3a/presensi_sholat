<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Presensi Thursina | @yield('title')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }
        
        /* Page Loader Styles */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
        }
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .loader-spinner {
            width: 3rem;
            height: 3rem;
            color: #198754;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Global Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="spinner-border loader-spinner" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hide loader on page load
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('hidden');
        });

        // Show loader on page transition (link clicks)
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a');
            if (target && target.href) {
                const hrefAttr = target.getAttribute('href');
                const hasDataToggle = target.hasAttribute('data-bs-toggle');
                
                if (hrefAttr && !hrefAttr.startsWith('#') && !hrefAttr.startsWith('javascript') && target.target !== '_blank' && !hasDataToggle) {
                    if (target.hostname === window.location.hostname) {
                        const loader = document.getElementById('page-loader');
                        if (loader) loader.classList.remove('hidden');
                    }
                }
            }
        });

        // Show loader on form submit
        document.addEventListener('submit', function(e) {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.remove('hidden');
        });

        // Handle browser back button (bfcache)
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                const loader = document.getElementById('page-loader');
                if (loader) loader.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
