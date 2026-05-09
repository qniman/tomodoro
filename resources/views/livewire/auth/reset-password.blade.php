<div class="auth-card">
    <div class="auth-card__brand">
        <span class="auth-card__brand-mark">
            <x-ui.icon name="tomato" :size="18" />
        </span>
        <span>Tomodoro</span>
    </div>

    <h1>Новый пароль</h1>
    <p class="auth-card__lead">Придумайте надёжный пароль для аккаунта <strong>{{ $email }}</strong></p>

    @if($error)
        <div class="auth-card__alert auth-card__alert--error">{{ $error }}</div>
    @endif

    <form class="auth-card__form" wire:submit.prevent="submit">
        <x-ui.input
            type="password"
            label="Новый пароль"
            name="password"
            icon="lock"
            wire:model="password"
            :error="$errors->first('password')"
            hint="Минимум 8 символов"
            autofocus
        />

        <x-ui.input
            type="password"
            label="Повторите пароль"
            name="password_confirmation"
            icon="key"
            wire:model="password_confirmation"
            :error="$errors->first('password_confirmation')"
        />

        <x-ui.button variant="primary" type="submit" size="lg" class="btn--block" wireTarget="submit">
            Сохранить пароль
        </x-ui.button>
    </form>

    <p class="auth-card__footer">
        <a href="{{ route('login') }}" wire:navigate>← Войти</a>
    </p>
</div>
