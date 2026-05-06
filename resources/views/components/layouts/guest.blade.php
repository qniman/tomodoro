@props(['title' => null])

<x-layouts.base :title="$title">
    <div class="auth-shell">
        {{ $slot }}
    </div>
</x-layouts.base>
