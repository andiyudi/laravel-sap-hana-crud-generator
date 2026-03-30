<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
        }

        body {
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            padding-top: 56px;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: 56px;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .nav-link {
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: var(--bs-secondary-bg);
        }

        .nav-link.active {
            background-color: var(--bs-primary);
            color: white !important;
        }

        .theme-toggle {
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        [data-bs-theme="dark"] .theme-toggle .bi-moon-fill {
            display: none;
        }

        [data-bs-theme="dark"] .theme-toggle .bi-sun-fill {
            display: inline-block;
        }

        [data-bs-theme="light"] .theme-toggle .bi-moon-fill {
            display: inline-block;
        }

        [data-bs-theme="light"] .theme-toggle .bi-sun-fill {
            display: none;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link d-md-none me-2" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-box-seam me-2"></i>
                {{ config('app.name', 'Laravel') }}
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <button class="btn btn-link theme-toggle p-0" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-moon-fill fs-5"></i>
                    <i class="bi bi-sun-fill fs-5"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="d-none d-md-inline ms-1">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ auth()->user()->email }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar bg-body-tertiary border-end" id="sidebar">
        <div class="p-3">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>

                <!-- Menu Builder Section -->
                @if(auth()->user()->hasRole('admin'))
                    <li class="nav-item mt-3">
                        <h6 class="px-3 text-muted text-uppercase small">Menu Builder</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tables.create') ? 'active' : '' }}" href="{{ route('tables.create') }}">
                            <i class="bi bi-plus-square me-2"></i>
                            Create Table
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tables.add-column') ? 'active' : '' }}" href="{{ route('tables.add-column') }}">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Column
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('menus.*') ? 'active' : '' }}" href="{{ route('menus.index') }}">
                            <i class="bi bi-list-ul me-2"></i>
                            Manage Menus
                        </a>
                    </li>
                @endif

                <!-- Dynamic Menus Section -->
                @php
                    $dynamicMenus = \App\Models\Menu::active()->ordered()->get();
                    $user = auth()->user();
                @endphp
                @if($dynamicMenus->isNotEmpty())
                    <li class="nav-item mt-3">
                        <h6 class="px-3 text-muted text-uppercase small">Dynamic Menus</h6>
                    </li>
                    @foreach($dynamicMenus as $dynamicMenu)
                        @php
                            $menuSlug = strtolower(str_replace(' ', '_', $dynamicMenu->name));
                            $canView = $user->hasRole('admin') || $user->can("{$menuSlug}.view");
                        @endphp
                        @if($canView)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('crud/' . $dynamicMenu->id . '*') ? 'active' : '' }}" href="{{ route('dynamic.index', $dynamicMenu->id) }}">
                                    <i class="bi {{ $dynamicMenu->icon ?? 'bi-table' }} me-2"></i>
                                    {{ $dynamicMenu->name }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif

                <!-- User Management Section -->
                @if(auth()->user()->hasRole('admin') || auth()->user()->can('users.view'))
                    <li class="nav-item mt-3">
                        <h6 class="px-3 text-muted text-uppercase small">User Management</h6>
                    </li>
                    @can('users.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-people me-2"></i>
                                Users
                            </a>
                        </li>
                    @endcan
                    @can('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <i class="bi bi-shield-check me-2"></i>
                                Roles
                            </a>
                        </li>
                    @endcan
                    @can('permissions.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                                <i class="bi bi-key me-2"></i>
                                Permissions
                            </a>
                        </li>
                    @endcan
                @endif

                <!-- System Section -->
                <li class="nav-item mt-3">
                    <h6 class="px-3 text-muted text-uppercase small">System</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}" href="{{ route('activity-log.index') }}">
                        <i class="bi bi-clock-history me-2"></i>
                        Activity Log
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;

        // Load saved theme or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
