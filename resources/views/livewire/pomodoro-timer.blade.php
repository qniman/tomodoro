<div class="space-y-6">
    <div class="panel space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs uppercase tracking-[0.4em] text-indigo-300">Pomodoro</p>
                <h2 class="text-2xl font-semibold">Режим концентрации</h2>
            </div>
            <span class="text-xs text-slate-400">Сессий: {{ $sessions->count() }}</span>
        </div>
        <form wire:submit.prevent="startSession" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="filter-label">Рабочее время</label>
                <input wire:model.defer="config.work_minutes" type="number" min="1" class="filter-input" />
            </div>
            <div>
                <label class="filter-label">Перерыв</label>
                <input wire:model.defer="config.break_minutes" type="number" min="1" class="filter-input" />
            </div>
            <div>
                <label class="filter-label">Кол-во циклов</label>
                <input wire:model.defer="config.pomodoros" type="number" min="1" class="filter-input" />
            </div>
            <div>
                <label class="filter-label">Задача</label>
                <select wire:model="selectedTaskId" class="filter-input">
                    <option value="">Без привязки</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>
            @if($recommendedPomodoros)
                <div class="md:col-span-4 text-xs text-slate-700 bg-slate-100 border border-slate-200 rounded-md px-4 py-3 flex items-center justify-between">
                    <span>Оценка: ~{{ $recommendedPomodoros }} помидор(ов)</span>
                    @if($estimatedMinutes)
                        <span>≈ {{ $estimatedMinutes }} мин с перерывами</span>
                    @endif
                </div>
            @endif
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="btn-primary">Запустить</button>
            </div>
        </form>
    </div>

    @if($active)
        <div id="pomodoro-active-{{ $active->id }}" class="rounded-md border {{ $active->isInBreak() ? 'border-blue-500/40' : 'border-emerald-500/40' }} bg-white px-5 py-4 text-sm" data-session-id="{{ $active->id }}" data-work-sec="{{ $active->work_sec }}" data-break-sec="{{ $active->break_sec }}" data-phase-started-at="{{ $active->phase_started_at?->toIso8601String() }}" data-phase="{{ $active->phase }}" data-paused="{{ $active->isPaused() ? 'true' : 'false' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-28 h-28 flex items-center justify-center">
                        <svg viewBox="0 0 36 36" class="w-20 h-20">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $active->isInBreak() ? '#dbeafe' : '#e6f4ef' }}" stroke-width="2" />
                            <path id="pomodoro-progress-{{ $active->id }}" class="circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $active->isInBreak() ? '#3b82f6' : '#059669' }}" stroke-width="2" stroke-dasharray="0 100" stroke-linecap="round" />
                            <text id="pomodoro-text-{{ $active->id }}" x="18" y="20.5" alignment-baseline="middle" text-anchor="middle" font-size="6" fill="{{ $active->isInBreak() ? '#1e40af' : '#065f46' }}">--:--</text>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide {{ $active->isInBreak() ? 'text-blue-200' : 'text-emerald-200' }}">{{ $active->isInBreak() ? 'Перерыв' : 'Активная сессия' }}</p>
                        <p class="text-lg font-semibold">{{ $active->task?->title ?? 'Фокус' }}</p>
                        <p class="text-slate-400">{{ $active->isInBreak() ? 'Отдыхайте' : 'Цикл ' . ($active->completed_pomodoros + 1) . ' из ' . $active->total_pomodoros }}</p>
                        @if($active->isPaused())
                            <p class="text-xs text-slate-500 mt-1">⏸ Пауза</p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 items-end">
                    @if($active->isPaused())
                        <button wire:click="resumeSession({{ $active->id }})" class="btn-secondary text-emerald-400 bg-emerald-50 border-emerald-400/40">Возобновить</button>
                    @else
                        <button wire:click="pauseSession({{ $active->id }})" class="btn-secondary text-slate-400 bg-slate-50 border-slate-400/40">⏸ Пауза</button>
                    @endif
                    @if($active->isInBreak())
                        <button wire:click="completeBreak({{ $active->id }})" class="btn-secondary text-blue-400 bg-blue-50 border-blue-400/40">К работе</button>
                    @else
                        <button wire:click="completePomodoro({{ $active->id }})" class="btn-secondary text-emerald-400 bg-emerald-50 border-emerald-400/40">✓ Готово</button>
                    @endif
                    <button wire:click="stopSession({{ $active->id }})" class="btn-secondary text-red-400 border-red-400/40">Стоп</button>
                </div>
            </div>
            <script>
                (function(){
                    const root = document.getElementById('pomodoro-active-{{ $active->id }}');
                    if (!root) return;
                    const isBreak = root.dataset.phase === 'break';
                    const maxSec = isBreak ? parseInt(root.dataset.breakSec || '{{ $active->break_sec }}', 10) : parseInt(root.dataset.workSec || '{{ $active->work_sec }}', 10);
                    let phaseStartedAt = new Date(root.dataset.phaseStartedAt || '{{ $active->phase_started_at?->toIso8601String() }}');
                    const progressPath = document.getElementById('pomodoro-progress-{{ $active->id }}');
                    const textEl = document.getElementById('pomodoro-text-{{ $active->id }}');
                    const sessionId = root.dataset.sessionId || '{{ $active->id }}';

                    let totalPausedTime = 0;
                    let hasNotified = false;

                    // Declare timers and audio context before use to avoid TDZ errors
                    let timer = null;
                    let syncInterval = null;
                    let audioContext = null;

                    function pad(n){ return n.toString().padStart(2,'0'); }

                    function ensureAudioContext() {
                        try {
                            if (!audioContext) {
                                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                            }
                            if (audioContext && audioContext.state === 'suspended' && typeof audioContext.resume === 'function') {
                                audioContext.resume().catch(()=>{});
                            }
                        } catch(e) {
                            audioContext = null;
                        }
                    }

                    // Try to initialize/resume AudioContext on first user gesture
                    function bindInitOnGesture() {
                        const init = function(){
                            ensureAudioContext();
                            document.removeEventListener('click', init);
                            document.removeEventListener('keydown', init);
                        };
                        document.addEventListener('click', init, { once: true });
                        document.addEventListener('keydown', init, { once: true });
                    }

                    bindInitOnGesture();

                    function playNotification() {
                        try {
                            ensureAudioContext();
                            if (audioContext) {
                                const oscillator = audioContext.createOscillator();
                                const gainNode = audioContext.createGain();
                                oscillator.connect(gainNode);
                                gainNode.connect(audioContext.destination);

                                oscillator.frequency.value = 800;
                                oscillator.type = 'sine';
                                const now = audioContext.currentTime || 0;
                                gainNode.gain.setValueAtTime(0.3, now);
                                gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.5);

                                oscillator.start(now);
                                oscillator.stop(now + 0.5);
                                return;
                            }
                        } catch(e) {
                            // fall through to notification-only path
                        }
                    }

                    function update(){
                        const now = new Date();
                        let elapsed = Math.floor((now - phaseStartedAt) / 1000) - totalPausedTime;
                        if (elapsed < 0) elapsed = 0;
                        const remaining = Math.max(0, maxSec - elapsed);
                        const minutes = Math.floor(remaining / 60);
                        const seconds = remaining % 60;
                        if (textEl) textEl.textContent = pad(minutes) + ':' + pad(seconds);

                        const total = maxSec;
                        const done = Math.min(total, elapsed);
                        const pct = total > 0 ? (done / total) * 100 : 0;
                        if (progressPath) {
                            const dash = pct.toFixed(2) + ' ' + (100 - pct).toFixed(2);
                            progressPath.setAttribute('stroke-dasharray', dash);
                        }

                        if (remaining <= 0 && !hasNotified) {
                            hasNotified = true;
                            playNotification();
                            if ('Notification' in window && Notification.permission === 'granted') {
                                const phaseText = isBreak ? 'Перерыв закончился!' : 'Рабочий цикл завершён!';
                                const msg = isBreak ? 'Пора вернуться к работе' : 'Отличная работа, пора на перерыв!';
                                try { new Notification('🍅 Pomodoro', { body: phaseText + '\n' + msg }); } catch(e) {}
                            }
                            if ('Notification' in window && Notification.permission === 'default') {
                                Notification.requestPermission().catch(()=>{});
                            }
                            if (timer) clearInterval(timer);
                            if (syncInterval) clearInterval(syncInterval);
                        }
                    }

                    update();
                    timer = setInterval(update, 1000);

                    syncInterval = setInterval(function(){
                        try {
                            const now2 = new Date();
                            let elapsed2 = Math.floor((now2 - phaseStartedAt) / 1000) - totalPausedTime;
                            if (elapsed2 < 0) elapsed2 = 0;
                            if (window.Livewire && typeof Livewire.emit === 'function') {
                                Livewire.emit('syncProgress', sessionId, elapsed2);
                            }
                        } catch(e){ }
                    }, 60000);
                })();
            </script>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
        @forelse($sessions as $session)
            <div class="rounded-md border border-slate-200 bg-white p-4 shadow-xl shadow-slate-200">
                <header class="flex justify-between items-center text-sm">
                    <strong>Сессия #{{ $session->id }}</strong>
                    <span class="text-slate-500">{{ $session->created_at->diffForHumans() }}</span>
                </header>
                <dl class="mt-3 text-sm text-slate-500 space-y-1">
                    <div class="flex justify-between">
                        <dt>Задача</dt>
                        <dd>{{ $session->task?->title ?? 'Без задачи' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Статус</dt>
                        <dd>{{ $session->status }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Рабочие минуты</dt>
                        <dd>{{ intdiv($session->work_sec, 60) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Перерыв</dt>
                        <dd>{{ intdiv($session->break_sec, 60) }}</dd>
                    </div>
                </dl>
                <div class="flex justify-end gap-2 mt-3">
                    @if($session->status != "finished")
                        <button wire:click="completePomodoro({{ $session->id }})" class="btn-secondary text-emerald-400 bg-emerald-50 border-emerald-400/40">+ цикл</button>
                        <button wire:click="stopSession({{ $session->id }})" class="btn-secondary text-red-400 bg-red-50 border-red-400/40">Завершить</button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 md:col-span-2">История пуста.</p>
        @endforelse
    </div>

    @if($showEstimateModal)
        <div class="modal-overlay">
            <div class="modal-panel max-w-md">
                <div class="modal-header">
                    <h3>Оценка времени</h3>
                    <button type="button" class="modal-close" wire:click="$set('showEstimateModal', false)">×</button>
                </div>
                <form wire:submit.prevent="saveEstimate" class="space-y-4">
                    <p class="text-sm text-slate-300">Укажите, сколько минут вам потребуется на выполнение этой задачи.</p>
                    <div>
                        <label class="filter-label">Минуты</label>
                        <input wire:model.defer="estimateMinutesInput" type="number" min="1" class="filter-input" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn-secondary" wire:click="$set('showEstimateModal', false)">Отмена</button>
                        <button type="submit" class="btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
