<div class="auth-card">
    <div class="auth-card__brand">
        <span class="auth-card__brand-mark">
            <x-ui.icon name="tomato" :size="18" />
        </span>
        <span>Tomodoro</span>
    </div>

    <h1>Войти</h1>
    <p class="auth-card__lead">Продолжайте свой день — задачи, помодоро и календарь под рукой.</p>

    <form class="auth-card__form" wire:submit.prevent="submit">
        <x-ui.input
            type="email"
            label="Email"
            name="email"
            icon="mail"
            wire:model="email"
            autocomplete="email"
            autofocus
            :error="$errors->first('email')"
        />

        <x-ui.input
            type="password"
            label="Пароль"
            name="password"
            icon="lock"
            wire:model="password"
            autocomplete="current-password"
            :error="$errors->first('password')"
        />

        <div class="hstack" style="justify-content: space-between;">
            <x-ui.checkbox wire:model="remember">Запомнить меня</x-ui.checkbox>
            {{-- Восстановление пароля — отдельным шагом --}}
        </div>

        <x-ui.button variant="primary" type="submit" size="lg" class="btn--block" wireTarget="submit">
            Войти
        </x-ui.button>
    </form>

    <p class="auth-card__footer">
        Нет аккаунта?
        <a href="{{ route('register') }}" wire:navigate>Создать</a>
    </p>
</div>
