<x-layouts.app title="Ingresar | REBA Lanzamientos">
    <div class="min-h-screen grid place-items-center p-6">
        <form class="panel w-full max-w-md p-8" method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="brand-mark mb-5">R</div>
            <h1 class="text-2xl font-medium">Ingresar a REBA Hub</h1>
            <p class="mt-2 text-sm" style="color: var(--muted)">Usa el usuario administrador sembrado por el sistema.</p>

            <label class="mt-6 block text-sm font-medium">Correo</label>
            <input class="input mt-2" name="email" type="email" value="{{ old('email', 'admin@rebagliati.edu.pe') }}" required>

            <label class="mt-4 block text-sm font-medium">Contrasena</label>
            <input class="input mt-2" name="password" type="password" value="password" required>

            <button class="btn btn-primary mt-6 w-full" type="submit">Entrar</button>
        </form>
    </div>
</x-layouts.app>
