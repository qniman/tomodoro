<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Tomodoro' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <p class="text-sm uppercase tracking-[0.4em] text-indigo-400">Tomodoro</p>
                <h1 class="text-2xl font-semibold mt-3">{{ $title ?? 'Вход' }}</h1>
                <p class="text-sm text-slate-400">Сервис управления задачами, временем и фокусом</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-md shadow p-8">
                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>
