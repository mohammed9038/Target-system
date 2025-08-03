<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Target Management System')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @if(app()->getLocale() === 'ar')
    <!-- RTL Support -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @endif
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            line-height: 1.6;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.show {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-bullseye text-white fs-3 me-2"></i>
                <div>
                    <h5 class="text-white mb-0">{{ __('Target') }}</h5>
                    <small class="text-white-50">{{ __('Management') }}</small>
                </div>
            </div>
            
            @auth
            <nav class="nav flex-column">
                <a href="{{ route('dashboard') }}" class="nav-link text-white-50 py-2">
                    <i class="bi bi-house me-2"></i>{{ __('Dashboard') }}
                </a>
                
                <a href="{{ route('targets.index') }}" class="nav-link text-white-50 py-2">
                    <i class="bi bi-target me-2"></i>{{ __('Targets') }}
                </a>
                
                @if(auth()->user()->isAdmin())
                <div class="nav-item">
                    <a class="nav-link text-white-50 py-2" data-bs-toggle="collapse" href="#masterDataMenu">
                        <i class="bi bi-gear me-2"></i>{{ __('Master Data') }}
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse" id="masterDataMenu">
                        <div class="nav flex-column ps-3">
                            <a href="{{ route('regions.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Regions') }}</a>
                            <a href="{{ route('channels.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Channels') }}</a>
                            <a href="{{ route('suppliers.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Suppliers') }}</a>
                            <a href="{{ route('categories.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Categories') }}</a>
                            <a href="{{ route('salesmen.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Salesmen') }}</a>
                            <a href="{{ route('periods.index') }}" class="nav-link text-white-50 py-1 small">{{ __('Periods') }}</a>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('users.index') }}" class="nav-link text-white-50 py-2">
                    <i class="bi bi-people me-2"></i>{{ __('Users') }}
                </a>
                @endif
            </nav>
            @endauth
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-md-none" type="button" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
            </div>
            
            @auth
            <div class="dropdown">
                <a class="btn btn-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->username }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text">
                            <small class="text-muted">{{ __('Role') }}: {{ ucfirst(auth()->user()->role) }}</small>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
    </script>
    
    @stack('scripts')
</body>
</html>
