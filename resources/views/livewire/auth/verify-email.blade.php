<div class="auth-card">
    <div class="auth-card__brand">
        <span class="auth-card__brand-mark">
            <x-ui.icon name="tomato" :size="18" />
        </span>
        <span>Tomodoro</span>
    </div>

    <h1>Подтвердите email</h1>
    <p class="auth-card__lead">
        Мы отправили 6-значный код на <strong>{{ auth()->user()->email }}</strong>
    </p>

    @if($error)
        <div class="auth-card__alert auth-card__alert--error">{{ $error }}</div>
    @endif

    @if($sent && !$error)
        <div class="auth-card__alert auth-card__alert--success">Новый код отправлен — проверьте почту.</div>
    @endif

    <form class="auth-card__form" wire:submit.prevent="submit">
        <x-ui.input
            type="text"
            label="Код из письма"
            name="code"
            icon="key"
            wire:model="code"
            placeholder="000000"
            maxlength="6"
            autofocus
        />

        <x-ui.button variant="primary" type="submit" size="lg" class="btn--block" wireTarget="submit">
            Подтвердить
        </x-ui.button>
    </form>

    <p class="auth-card__footer">
        Не получили письмо?
        <button type="button" wire:click="resend" class="link-btn">Отправить повторно</button>
    </p>

    <p class="auth-card__footer">
        <a href="{{ route('app') }}" wire:navigate>Пропустить →</a>
    </p>
</div>
