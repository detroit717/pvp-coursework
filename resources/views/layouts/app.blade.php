<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ПВП - Управление')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --warning: #ffc107;
            --danger: #dc3545;
            --dark: #212529;
            --gray-50: #f8f9fa;
            --gray-100: #f1f3f5;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --sidebar-width: 260px;
        }

        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

        body {
            background: var(--gray-50);
            padding-top: 64px;
            overflow-x: hidden;
        }

        .navbar-top {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            height: 64px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.15);
            z-index: 1030;
        }

        .navbar-top .navbar-brand {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #fff, #64b5f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - 64px);
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            padding: 1rem 0;
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 0.75rem 1.25rem;
            margin: 2px 8px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(13,110,253,0.3);
        }

        .sidebar .nav-link i { font-size: 1.2rem; width: 24px; text-align: center; }

        .sidebar .nav-section { font-size: .65rem; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.3); padding: 1rem 1.25rem .5rem; font-weight: 700; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem 2rem;
            transition: margin-left 0.3s;
        }

        .page-header { margin-bottom: 1.5rem; }
        .page-header h2 { font-weight: 700; color: var(--dark); font-size: 1.6rem; }
        .page-header .breadcrumb { background: none; padding: 0; margin: 0; }
        .page-header .breadcrumb-item a { color: var(--primary); text-decoration: none; font-size: 0.85rem; }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(.4,0,.2,1);
        }

        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }

        .stat-card .stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; margin: 0; line-height: 1.2; }

        .card-modern {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.3s;
        }

        .card-modern:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.06); }

        .card-modern .card-header {
            background: none;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-modern {
            border-radius: 10px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-modern:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        .table-modern thead th {
            background: var(--gray-100);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            border-bottom: none;
            padding: 0.85rem 1rem;
        }

        .table-modern td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--gray-100); }

        .badge-modern {
            padding: 0.35em 0.85em;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .modal-modern .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .modal-modern .modal-header {
            border-radius: 16px 16px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-modern .modal-body { padding: 1.5rem; }
        .modal-modern .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-100); }

        .avatar-sq {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem;
        }

        .form-control-modern {
            border-radius: 10px;
            border: 2px solid var(--gray-200);
            padding: 0.6rem 1rem;
            transition: all 0.2s;
        }

        .form-control-modern:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        }

        .lane-card {
            background: #fff;
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .lane-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 20px rgba(13,110,253,0.1);
            transform: translateX(4px);
        }

        .lane-card .lane-icon {
            width: 56px; height: 56px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
        }

        .page-transition { animation: pageIn 0.3s ease; }
        @keyframes pageIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .hidden { display: none; }

        .toast-container { position: fixed; top: 80px; right: 20px; z-index: 9999; }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #adb5bd;
        }

        .empty-state i { font-size: 3rem; margin-bottom: 1rem; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1rem; }
        }

        .footer {
            margin-left: var(--sidebar-width);
            background: #fff;
            border-top: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            font-size: 0.8rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .footer { margin-left: 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-top fixed-top">
        <div class="container-fluid px-4">
            <button class="btn btn-link text-white p-0 me-3 d-md-none" type="button" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-shield-fill-check me-2"></i>ПВП
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small d-none d-md-block">
                    <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i>
                    Система управления
                </span>
            </div>
        </div>
    </nav>

    <aside class="sidebar" id="sidebar">
        <div class="nav-section">Навигация</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>Главная
        </a>
        <a href="{{ route('payment_points.index') }}" class="nav-link {{ request()->routeIs('payment_points.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>Пункты оплаты
        </a>
        <a href="{{ route('drivers.index') }}" class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>Водители
        </a>
        <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>Автомобили
        </a>
        <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i class="bi bi-currency-exchange"></i>Транзакции
        </a>
        <a href="{{ route('fines.index') }}" class="nav-link {{ request()->routeIs('fines.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-octagon"></i>Штрафы
        </a>
        <a href="{{ route('transponders.index') }}" class="nav-link {{ request()->routeIs('transponders.*') ? 'active' : '' }}">
            <i class="bi bi-wifi"></i>Транспондеры
        </a>

        <div class="nav-section mt-3">Аналитика</div>
        <a href="{{ route('statistics.index') }}" class="nav-link {{ request()->routeIs('statistics.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i>Статистика
        </a>
    </aside>

    <main class="main-content page-transition">
        @yield('content')
    </main>

    <div class="footer">
        <div class="d-flex justify-content-between align-items-center">
            <span>&copy; {{ date('Y') }} ПВП Управление. Все права защищены.</span>
            <span class="text-muted">v2.0.0</span>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-circle-fill', info: 'bi-info-circle-fill' };
            const colors = { success: '#198754', error: '#dc3545', warning: '#ffc107', info: '#0dcaf0' };
            const id = 'toast-' + Date.now();
            const html = `
                <div id="${id}" class="toast align-items-center text-white border-0 show" role="alert" style="background: ${colors[type]}; border-radius: 12px;">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center gap-2">
                            <i class="bi ${icons[type]} fs-5"></i>
                            <span>${message}</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            setTimeout(() => { const el = document.getElementById(id); if (el) { el.remove(); } }, 4000);
        }

        function confirmDelete(url, name, callback) {
            if (confirm(`Вы уверены, что хотите удалить "${name}"?`)) {
                if (callback) callback();
                else if (url) window.location.href = url;
            }
        }

        $.fn.dataTable.ext.errMode = 'none';
    </script>

    @stack('scripts')
</body>
</html>
