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
    <div class="app-shell">
        <aside class="sidebar">
            <div class="flex items-center gap-3">
                <div class="brand-mark">R</div>
                <div>
                    <p class="font-medium">REBA Hub</p>
                    <p class="text-sm" style="color: var(--muted)">Lanzamientos</p>
                </div>
            </div>

            <nav class="mt-8 grid gap-2">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('launches.*') ? 'active' : '' }}" href="{{ route('launches.index') }}">Lanzamientos</a>
                <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">Calendario</a>
                <a class="nav-link {{ request()->routeIs('conflicts.*') ? 'active' : '' }}" href="{{ route('conflicts.index') }}">Conflictos</a>
                <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}">Aprobaciones</a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.monthly') }}">Reportes</a>
            </nav>

            <form class="mt-8" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-secondary w-full" type="submit">Cerrar sesion</button>
            </form>
        </aside>

        <main class="main">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-medium">{{ $heading ?? 'Panel operativo' }}</h1>
                    <p class="mt-1 text-sm" style="color: var(--muted)">{{ $subheading ?? 'Planificacion, validacion y aprobacion de eventos academicos.' }}</p>
                </div>
                <div class="text-sm" style="color: var(--muted)">{{ auth()->user()->name }}</div>
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
