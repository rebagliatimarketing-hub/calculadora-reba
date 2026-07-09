<div class="card p-5">
    <p class="text-sm" style="color: var(--muted)">{{ $label }}</p>
    <p class="mt-3 text-3xl font-medium">{{ $value }}</p>
    @isset($hint)
        <p class="mt-2 text-sm" style="color: var(--muted)">{{ $hint }}</p>
    @endisset
</div>
