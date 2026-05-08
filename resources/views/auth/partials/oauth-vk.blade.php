@if (session('vk_error'))
    <p class="auth-card__alert" role="alert">{{ session('vk_error') }}</p>
@endif

@php
    $vkConfigured = filled(config('services.vkontakte.client_id'))
        && filled(config('services.vkontakte.client_secret'))
        && filled(config('services.vkontakte.redirect'));
@endphp

<div class="auth-card__oauth-block">
    @if ($vkConfigured)
        <div class="auth-card__oauth-divider" role="presentation">
            <span>или войти через</span>
        </div>

        <a href="{{ route('auth.vk.redirect') }}" class="btn-vk">
            <span class="btn-vk__glyph" aria-hidden="true"></span>
            <span>Войти через VK</span>
        </a>
    @else
        <div class="auth-card__oauth-divider" role="presentation">
            <span>или войти через</span>
        </div>
        <p class="auth-card__muted auth-card__oauth-hint">
            Для входа через VK добавьте <code>VKONTAKTE_CLIENT_ID</code>, секрет и redirect в <code>.env</code>.
        </p>
    @endif
</div>
