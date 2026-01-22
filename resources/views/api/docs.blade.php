<x-layouts.app title="API и интеграции">
    <section class="panel space-y-4">
        <div>
            <h2 class="panel-title">REST API Tomodoro</h2>
            <p class="panel-subtitle">Sanctum-токены обеспечивают доступ извне. Используйте эндпоинты ниже.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div class="api-card">
                <p class="api-verb">POST</p>
                <div>
                    <p class="font-semibold">/api/auth/login</p>
                    <p class="text-slate-400 text-xs">Получить токен по email/паролю.</p>
                </div>
            </div>
            <div class="api-card">
                <p class="api-verb get">GET</p>
                <div>
                    <p class="font-semibold">/api/tasks</p>
                    <p class="text-slate-400 text-xs">Фильтрация по тегу, статусу, категории.</p>
                </div>
            </div>
            <div class="api-card">
                <p class="api-verb post">POST</p>
                <div>
                    <p class="font-semibold">/api/pomodoro/start</p>
                    <p class="text-slate-400 text-xs">Запустить таймер (work_minutes, break_minutes, pomodoros).</p>
                </div>
            </div>
            <div class="api-card">
                <p class="api-verb get">GET</p>
                <div>
                    <p class="font-semibold">/api/calendar/events</p>
                    <p class="text-slate-400 text-xs">Вывести события за диапазон дат.</p>
                </div>
            </div>
        </div>
        <div class="rounded-md border border-slate-200 bg-white px-4 py-4 text-xs font-mono text-slate-700">
            <p class="text-slate-400 mb-2">Пример запроса:</p>
            <pre>
curl -H "Authorization: Bearer &lt;token&gt;" \
     -H "Accept: application/json" \
     https://tomodoro.test/api/tasks?status=pending
            </pre>
        </div>
        <p class="text-xs text-slate-500">
            Полную спецификацию можно собрать автоматически через <code class="text-slate-300">php artisan route:list --path=api</code>.
        </p>
    </section>
</x-layouts.app>
