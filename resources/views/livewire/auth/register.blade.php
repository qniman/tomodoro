<div class="auth-card">
    <div class="auth-card__brand">
        <span class="auth-card__brand-mark">
            <x-ui.icon name="tomato" :size="18" />
        </span>
        <span>Tomodoro</span>
    </div>

    <h1>Создать аккаунт</h1>
    <p class="auth-card__lead">Минута на регистрацию — и приступаем к фокусу.</p>

    <form class="auth-card__form" wire:submit.prevent="submit">
        <x-ui.input
            type="text"
            label="Имя"
            name="name"
            icon="user"
            wire:model="name"
            autocomplete="name"
            autofocus
            :error="$errors->first('name')"
        />

        <x-ui.input
            type="email"
            label="Email"
            name="email"
            icon="mail"
            wire:model.blur="email"
            autocomplete="email"
            :error="$errors->first('email')"
        />

        <x-ui.input
            type="password"
            label="Пароль"
            name="password"
            icon="lock"
            wire:model="password"
            autocomplete="new-password"
            :error="$errors->first('password')"
            hint="Минимум 8 символов"
        />

        <x-ui.input
            type="password"
            label="Повторите пароль"
            name="password_confirmation"
            icon="key"
            wire:model="password_confirmation"
            autocomplete="new-password"
            :error="$errors->first('password_confirmation')"
        />

        <x-ui.button variant="primary" type="submit" size="lg" class="btn--block" wireTarget="submit">
            Создать
        </x-ui.button>
    </form>

    <p class="auth-card__footer">
        Уже есть аккаунт?
        <a href="{{ route('login') }}" wire:navigate>Войти</a>
    </p>
</div>
