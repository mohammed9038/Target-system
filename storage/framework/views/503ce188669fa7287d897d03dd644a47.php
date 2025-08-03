<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Target Management System')); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* High contrast, accessible colors */
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary-color: #4b5563;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --info-color: #0891b2;
            
            /* Improved background colors */
            --light-bg: #ffffff;
            --light-bg-secondary: #f9fafb;
            --dark-bg: #111827;
            --dark-bg-secondary: #1f2937;
            
            /* Better text contrast */
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6b7280;
            --text-light: #9ca3af;
            
            /* Enhanced borders and dividers */
            --border-color: #d1d5db;
            --border-light: #e5e7eb;
            --border-dark: #9ca3af;
            
            /* Improved shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.15), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.2), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            /* Status colors with better contrast */
            --success-bg: #ecfdf5;
            --success-text: #065f46;
            --warning-bg: #fef3c7;
            --warning-text: #92400e;
            --danger-bg: #fef2f2;
            --danger-text: #991b1b;
            --info-bg: #f0f9ff;
            --info-text: #1e40af;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
        }

        .sidebar-user {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            padding: 0 1.5rem 0.5rem;
            margin-bottom: 0.5rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            margin: 0.125rem 1rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        [dir="rtl"] .sidebar .nav-link:hover {
            transform: translateX(-4px);
        }

        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: var(--shadow-sm);
        }

        .sidebar .nav-link i {
            width: 1.25rem;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        [dir="rtl"] .sidebar .nav-link i {
            margin-right: 0;
            margin-left: 0.75rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        [dir="rtl"] .main-content {
            margin-left: 0;
            margin-right: 280px;
        }

        .content-wrapper {
            padding: 2rem;
            padding-top: 4rem; /* Add extra top padding for language switcher */
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: 0.75rem;
            background: white;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #312e81 100%);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }



        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 100%;
            }

            [dir="rtl"] .sidebar {
                transform: translateX(100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            [dir="rtl"] .main-content {
                margin-right: 0;
            }

            .content-wrapper {
                padding: 1rem;
                padding-top: 1rem;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1002;
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            box-shadow: var(--shadow-md);
        }

        [dir="rtl"] .mobile-menu-toggle {
            left: auto;
            right: 1rem;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Table Improvements */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead th {
            background-color: var(--light-bg);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        /* Badge Improvements */
        .badge {
            font-weight: 500;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        /* Alert Improvements */
        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        /* Form Improvements */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Overlay for mobile menu */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Enhanced Readability & Accessibility */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-secondary {
            color: var(--text-secondary) !important;
        }

        /* High contrast badges */
        .badge.bg-primary {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .badge.bg-success {
            background-color: var(--success-color) !important;
            color: white !important;
        }

        .badge.bg-warning {
            background-color: var(--warning-color) !important;
            color: white !important;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
            color: white !important;
        }

        .badge.bg-info {
            background-color: var(--info-color) !important;
            color: white !important;
        }

        /* Better contrast for light badge variants */
        .badge.bg-primary.bg-opacity-10 {
            background-color: var(--info-bg) !important;
            color: var(--info-text) !important;
            border: 1px solid var(--border-light);
        }

        .badge.bg-success.bg-opacity-10 {
            background-color: var(--success-bg) !important;
            color: var(--success-text) !important;
            border: 1px solid var(--border-light);
        }

        .badge.bg-warning.bg-opacity-10 {
            background-color: var(--warning-bg) !important;
            color: var(--warning-text) !important;
            border: 1px solid var(--border-light);
        }

        .badge.bg-danger.bg-opacity-10 {
            background-color: var(--danger-bg) !important;
            color: var(--danger-text) !important;
            border: 1px solid var(--border-light);
        }

        .badge.bg-info.bg-opacity-10 {
            background-color: var(--info-bg) !important;
            color: var(--info-text) !important;
            border: 1px solid var(--border-light);
        }

        /* Enhanced table readability */
        .table {
            font-size: 0.95rem;
        }

        .table th {
            color: var(--text-primary);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-light);
        }

        /* Better button contrast */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
            font-weight: 500;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Enhanced form input readability */
        .form-control, .form-select {
            color: var(--text-primary);
            background-color: var(--light-bg);
            border: 1px solid var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            color: var(--text-primary);
        }

        /* Better alert readability */
        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid #fca5a5;
        }

        .alert-warning {
            background-color: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid #fcd34d;
        }

        .alert-info {
            background-color: var(--info-bg);
            color: var(--info-text);
            border: 1px solid #93c5fd;
        }

        /* Improved card readability */
        .card {
            background-color: var(--light-bg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
        }

        /* Enhanced gradient readability */
        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
        }
    </style>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle d-md-none" id="mobile-menu-toggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>



    <?php if(auth()->guard()->check()): ?>
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-brand">
                    <i class="bi bi-target me-2"></i><?php echo e(__('Target Management')); ?>

                </a>
                <p class="sidebar-user"><?php echo e(Auth::user()->username); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <?php if(Auth::user()->isAdmin()): ?>
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(__('Master Data')); ?></div>
                        <a class="nav-link <?php echo e(request()->routeIs('regions.*') ? 'active' : ''); ?>" href="<?php echo e(route('regions.index')); ?>">
                            <i class="bi bi-geo-alt"></i><?php echo e(__('Regions')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('channels.*') ? 'active' : ''); ?>" href="<?php echo e(route('channels.index')); ?>">
                            <i class="bi bi-diagram-3"></i><?php echo e(__('Channels')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('suppliers.*') ? 'active' : ''); ?>" href="<?php echo e(route('suppliers.index')); ?>">
                            <i class="bi bi-building"></i><?php echo e(__('Suppliers')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('categories.index')); ?>">
                            <i class="bi bi-tags"></i><?php echo e(__('Categories')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('salesmen.*') ? 'active' : ''); ?>" href="<?php echo e(route('salesmen.index')); ?>">
                            <i class="bi bi-people"></i><?php echo e(__('Salesmen')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('periods.*') ? 'active' : ''); ?>" href="<?php echo e(route('periods.index')); ?>">
                            <i class="bi bi-calendar"></i><?php echo e(__('Periods')); ?>

                        </a>
                        <a class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>" href="<?php echo e(route('users.index')); ?>">
                            <i class="bi bi-person-gear"></i><?php echo e(__('Users')); ?>

                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('Operations')); ?></div>
                    <a class="nav-link <?php echo e(request()->routeIs('targets.*') ? 'active' : ''); ?>" href="<?php echo e(route('targets.index')); ?>">
                        <i class="bi bi-target"></i><?php echo e(__('Targets')); ?>

                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('Account')); ?></div>
                    <a class="nav-link" href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i><?php echo e(__('Logout')); ?>

                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper fade-in">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Login Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
        <?php echo csrf_field(); ?>
    </form>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (mobileMenuToggle && sidebar && overlay) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }

            // Add loading states to forms
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });
    </script>
</body>
</html> <?php /**PATH C:\Target\resources\views/layouts/app.blade.php ENDPATH**/ ?>