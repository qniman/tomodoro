<x-layouts.guest title="Регистрация">
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf
        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full rounded-md border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-md border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Пароль</label>
            <input type="password" name="password" required
                   class="w-full rounded-md border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <div class="space-y-2">
            <label class="text-xs uppercase tracking-wide text-slate-400">Подтверждение пароля</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-[7px] border border-slate-300 bg-slate-100 px-4 py-3 focus:border-indigo-400 focus:outline-none" />
        </div>
        <button type="submit"
                class="w-full rounded-[7px] text-white bg-indigo-500 hover:bg-indigo-400 transition font-semibold py-3">
            Зарегистрироваться
        </button>
        <p class="text-sm text-center text-slate-400">
            Уже есть аккаунт?
            <a href="{{ route('login') }}" class="text-indigo-300 hover:text-indigo-200">Войти</a>
        </p>
    </form>
</x-layouts.guest>
