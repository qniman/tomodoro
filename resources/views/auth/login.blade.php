<x-layouts.guest title="Вход в Tomodoro">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf
        @if ($errors->any())
            <div class="rounded-[7px]  border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-[7px]  border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Пароль</label>
            <input type="password" name="password" required
                   class="w-full rounded-[7px]  border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <div class="flex items-center justify-between text-sm text-slate-400">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-indigo-500">
                <span>Запомнить</span>
            </label>
            <a href="{{ route('register') }}" class="text-indigo-300 hover:text-indigo-200">Создать аккаунт</a>
        </div>
        <button type="submit"
                class="w-full rounded-xl text-white bg-indigo-500 hover:bg-indigo-400 transition font-semibold py-3">
            Войти
        </button>
    </form>
</x-layouts.guest>
