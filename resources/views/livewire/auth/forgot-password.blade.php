<div class="auth-card">
    <div class="auth-card__brand">
        <span class="auth-card__brand-mark">
            <x-ui.icon name="tomato" :size="18" />
        </span>
        <span>Tomodoro</span>
    </div>

    @if($sent)
        <h1>Письмо отправлено</h1>
        <p class="auth-card__lead">
            Если аккаунт с таким email существует — ссылка для сброса пароля уже в пути.
            Проверьте папку «Спам», если письмо не пришло через пару минут.
        </p>
        <div style="margin-top:24px;">
            <x-ui.button variant="ghost" size="lg" class="btn--block" href="{{ route('login') }}" wireNavigate>
                ← Вернуться ко входу
            </x-ui.button>
        </div>
    @else
        <h1>Забыли пароль?</h1>
        <p class="auth-card__lead">Введите email — мы пришлём ссылку для сброса пароля.</p>

        <form class="auth-card__form" wire:submit.prevent="submit">
            <x-ui.input
                type="email"
                label="Email"
                name="email"
                icon="mail"
                wire:model.blur="email"
                :error="$errors->first('email')"
                autofocus
            />

            <x-ui.button variant="primary" type="submit" size="lg" class="btn--block" wireTarget="submit">
                Отправить ссылку
            </x-ui.button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('login') }}" wire:navigate>← Вернуться ко входу</a>
        </p>
    @endif
</div>
