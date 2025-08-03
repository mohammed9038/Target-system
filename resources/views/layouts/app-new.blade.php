<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Target Management System') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Professional Color Palette */
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-800: #1e40af;
            --primary-900: #1e3a8a;
            
            /* Secondary Colors */
            --secondary: #64748b;
            --secondary-50: #f8fafc;
            --secondary-100: #f1f5f9;
            --secondary-200: #e2e8f0;
            --secondary-300: #cbd5e1;
            --secondary-400: #94a3b8;
            --secondary-500: #64748b;
            --secondary-600: #475569;
            --secondary-700: #334155;
            --secondary-800: #1e293b;
            --secondary-900: #0f172a;
            
            /* Status Colors */
            --success: #10b981;
            --success-50: #ecfdf5;
            --success-100: #d1fae5;
            --success-500: #10b981;
            --success-600: #059669;
            
            --warning: #f59e0b;
            --warning-50: #fffbeb;
            --warning-100: #fef3c7;
            --warning-500: #f59e0b;
            --warning-600: #d97706;
            
            --danger: #ef4444;
            --danger-50: #fef2f2;
            --danger-100: #fee2e2;
            --danger-500: #ef4444;
            --danger-600: #dc2626;
            
            --info: #06b6d4;
            --info-50: #ecfeff;
            --info-100: #cffafe;
            --info-500: #06b6d4;
            --info-600: #0891b2;
            
            /* Typography */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Shadows */
            --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            
            /* Layout */
            --sidebar-width: 280px;
            --topbar-height: 80px;
        }

        /* Global Styles */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            font-size: var(--font-size-base);
            line-height: 1.6;
            color: var(--secondary-700);
            background-color: var(--secondary-50);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Layout Structure */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .app-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-800) 0%, var(--primary-900) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: var(--spacing-sm);
            font-size: 1.25rem;
        }

        .sidebar-brand-text {
            font-size: var(--font-size-lg);
            font-weight: 700;
            line-height: 1.2;
        }

        .sidebar-user {
            margin-top: var(--spacing-sm);
            padding: var(--spacing-sm) 0;
            font-size: var(--font-size-sm);
            color: rgba(255, 255, 255, 0.8);
        }

        .sidebar-nav {
            padding: var(--spacing-md) 0;
        }

        .nav-section {
            margin-bottom: var(--spacing-lg);
        }

        .nav-section-title {
            font-size: var(--font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.6);
            padding: 0 var(--spacing-lg) var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-lg);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: var(--radius-md);
            margin: 2px var(--spacing-sm);
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            font-weight: 600;
        }

        .nav-link i {
            width: 20px;
            margin-right: var(--spacing-sm);
            font-size: 1.1rem;
        }

        /* Main Content */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
        }

        .app-topbar {
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid var(--secondary-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .app-content {
            flex: 1;
            padding: var(--spacing-xl);
            overflow-y: auto;
        }

        /* Page Header Component */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--secondary-200);
        }

        .page-header-content h1 {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--secondary-900);
            margin: 0 0 var(--spacing-xs) 0;
            display: flex;
            align-items: center;
        }

        .page-header-icon {
            width: 36px;
            height: 36px;
            background: var(--primary-50);
            color: var(--primary-600);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: var(--spacing-sm);
        }

        .page-header-description {
            color: var(--secondary-500);
            font-size: var(--font-size-base);
            margin: 0;
        }

        .page-header-actions {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }

        /* Card Components */
        .card {
            background: white;
            border: 1px solid var(--secondary-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-header {
            background: var(--secondary-50);
            border-bottom: 1px solid var(--secondary-200);
            padding: var(--spacing-lg);
        }

        .card-title {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--secondary-900);
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: var(--spacing-sm);
            color: var(--primary-600);
        }

        .card-body {
            padding: var(--spacing-lg);
        }

        /* Button Styles */
        .btn {
            font-weight: 500;
            border-radius: var(--radius-md);
            padding: var(--spacing-sm) var(--spacing-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            transition: all 0.2s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--primary-600);
            border-color: var(--primary-600);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-700);
            border-color: var(--primary-700);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-primary {
            background: transparent;
            border-color: var(--primary-600);
            color: var(--primary-600);
        }

        .btn-outline-primary:hover {
            background: var(--primary-600);
            border-color: var(--primary-600);
            color: white;
        }

        .btn-success {
            background: var(--success-600);
            border-color: var(--success-600);
            color: white;
        }

        .btn-success:hover {
            background: var(--success-700);
            border-color: var(--success-700);
            color: white;
        }

        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: var(--font-size-sm);
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid var(--secondary-300);
            border-radius: var(--radius-md);
            padding: var(--spacing-sm) var(--spacing-sm);
            font-size: var(--font-size-base);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-700);
            margin-bottom: var(--spacing-xs);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: var(--radius-lg);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: flex-start;
        }

        .alert-success {
            background: var(--success-50);
            color: var(--success-800);
            border-left: 4px solid var(--success-500);
        }

        .alert-danger {
            background: var(--danger-50);
            color: var(--danger-800);
            border-left: 4px solid var(--danger-500);
        }

        .alert-warning {
            background: var(--warning-50);
            color: var(--warning-800);
            border-left: 4px solid var(--warning-500);
        }

        .alert-info {
            background: var(--info-50);
            color: var(--info-800);
            border-left: 4px solid var(--info-500);
        }

        /* Table Styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: var(--secondary-50);
            color: var(--secondary-700);
            font-weight: 600;
            border-bottom: 1px solid var(--secondary-200);
            padding: var(--spacing-sm) var(--spacing-md);
        }

        .table td {
            padding: var(--spacing-sm) var(--spacing-md);
            border-bottom: 1px solid var(--secondary-100);
        }

        .table tbody tr:hover {
            background: var(--secondary-50);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .app-sidebar.show {
                transform: translateX(0);
            }

            .app-main {
                margin-left: 0;
            }

            .page-header {
                flex-direction: column;
                gap: var(--spacing-md);
                align-items: stretch;
            }

            .page-header-actions {
                justify-content: stretch;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 576px) {
            .app-content {
                padding: var(--spacing-md);
            }

            .page-header-content h1 {
                font-size: var(--font-size-2xl);
            }

            .btn {
                font-size: var(--font-size-sm);
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--secondary-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-300);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-400);
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
    <div class="app-layout">
        <!-- Sidebar -->
        <nav class="app-sidebar">
            <div class="sidebar-header">
                <a href="{{ route('targets.index') }}" class="sidebar-brand">
                    <div class="sidebar-brand-icon">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <div class="sidebar-brand-text">
                        Target<br>System
                    </div>
                </a>
                <div class="sidebar-user">
                    <div class="fw-medium">{{ auth()->user()->username ?? 'User' }}</div>
                    <div class="text-muted small">{{ ucfirst(auth()->user()->role ?? 'User') }}</div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets.*') ? 'active' : '' }}">
                        <i class="bi bi-bullseye"></i>
                        Sales Targets
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Master Data</div>
                    <a href="{{ route('salesmen.index') }}" class="nav-link {{ request()->routeIs('salesmen.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        Salesmen
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        Suppliers
                    </a>
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        Categories
                    </a>
                    <a href="{{ route('channels.index') }}" class="nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        Channels
                    </a>
                    <a href="{{ route('regions.index') }}" class="nav-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                        <i class="bi bi-geo"></i>
                        Regions
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <a href="{{ route('periods.index') }}" class="nav-link {{ request()->routeIs('periods.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-range"></i>
                        Periods
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i>
                        Users
                    </a>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="app-main">
            <!-- Top Bar -->
            <header class="app-topbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-lg-none me-3" type="button" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h6 class="mb-0 text-muted d-none d-md-block">
                        @yield('title', 'Target Management System')
                    </h6>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none d-flex align-items-center" 
                                type="button" 
                                data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 36px; height: 36px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="ms-2 text-start d-none d-md-block">
                                <div class="fw-medium text-dark">{{ auth()->user()->username ?? 'User' }}</div>
                                <small class="text-muted">{{ ucfirst(auth()->user()->role ?? 'User') }}</small>
                            </div>
                            <i class="bi bi-chevron-down ms-2 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="app-content">
                @yield('content')
            </main>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.app-sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
