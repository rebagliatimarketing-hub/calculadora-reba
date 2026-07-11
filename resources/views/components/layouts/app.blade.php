<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'REBA Lanzamientos' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@auth
    <a class="skip-link" href="#main-content">Ir al contenido principal</a>

    <div class="app-shell" data-sidebar-shell>
        <button class="sidebar-backdrop" type="button" aria-label="Cerrar menu lateral" data-sidebar-close></button>

        <aside class="sidebar" id="app-sidebar" aria-label="Menu principal">
            <div class="sidebar-header">
                <a class="sidebar-brand" href="{{ route('dashboard') }}" aria-label="Ir al Dashboard de REBA Hub">
                    <span class="brand-mark" aria-hidden="true">R</span>
                    <span class="brand-copy">
                        <span class="block font-medium">REBA Hub</span>
                        <span class="block text-sm" style="color: var(--muted)">Lanzamientos</span>
                    </span>
                </a>

                <button
                    class="sidebar-toggle"
                    type="button"
                    aria-controls="app-sidebar"
                    aria-expanded="true"
                    aria-label="Contraer menu lateral"
                    data-sidebar-toggle
                    data-tooltip="Contraer menu"
                >
                    <span class="hamburger-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <nav class="sidebar-nav" aria-label="Navegacion principal">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-tooltip="Dashboard" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="layout-dashboard" aria-hidden="true"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a class="nav-link {{ request()->routeIs('launches.*') ? 'active' : '' }}" href="{{ route('launches.index') }}" data-tooltip="Lanzamientos" @if(request()->routeIs('launches.*')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="rocket" aria-hidden="true"></i>
                    <span class="nav-label">Lanzamientos</span>
                </a>
                <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}" data-tooltip="Calendario" @if(request()->routeIs('calendar.*')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="calendar-days" aria-hidden="true"></i>
                    <span class="nav-label">Calendario</span>
                </a>
                <a class="nav-link {{ request()->routeIs('conflicts.*') ? 'active' : '' }}" href="{{ route('conflicts.index') }}" data-tooltip="Conflictos" @if(request()->routeIs('conflicts.*')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="triangle-alert" aria-hidden="true"></i>
                    <span class="nav-label">Conflictos</span>
                </a>
                <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}" data-tooltip="Aprobaciones" @if(request()->routeIs('approvals.*')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="badge-check" aria-hidden="true"></i>
                    <span class="nav-label">Aprobaciones</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.monthly') }}" data-tooltip="Reportes" @if(request()->routeIs('reports.*')) aria-current="page" @endif>
                    <i class="nav-icon" data-lucide="chart-no-axes-column" aria-hidden="true"></i>
                    <span class="nav-label">Reportes</span>
                </a>
            </nav>

            <form class="sidebar-footer" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link nav-logout" type="submit" data-tooltip="Cerrar sesion">
                    <i class="nav-icon" data-lucide="log-out" aria-hidden="true"></i>
                    <span class="nav-label">Cerrar sesion</span>
                </button>
            </form>
        </aside>

        <main class="main" id="main-content" tabindex="-1">
            <header class="page-header">
                <div>
                    <h1 class="text-3xl font-medium">{{ $heading ?? 'Panel operativo' }}</h1>
                    <p class="mt-1 text-sm" style="color: var(--muted)">{{ $subheading ?? 'Planificacion, validacion y aprobacion de eventos academicos.' }}</p>
                </div>
                <div class="user-chip" aria-label="Usuario actual: {{ auth()->user()->name }}">
                    <span class="user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                </div>
            </header>

            @if (session('status'))
                <div class="panel mb-5 p-4" style="border-color: var(--accent); color: var(--accent)">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="panel mb-5 p-4" style="border-color: var(--danger); color: var(--danger)">
                    {{ $errors->first() }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
@else
    {{ $slot }}
@endauth
</body>
</html>
