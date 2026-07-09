<span {{ $attributes->merge(['class' => 'status-pill']) }}>
    {{ str_replace('_', ' ', $slot) }}
</span>
