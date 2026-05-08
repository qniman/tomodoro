<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\VkIdUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class VkOAuthController extends Controller
{
    public function __construct(
        protected VkIdUserService $vkIdUsers,
    ) {}

    /**
     * Редирект на VK ID (id.vk.ru) с PKCE; далее callback обменивает code вручную (см. VK ID «без SDK»).
     */
    public function redirect(): RedirectResponse
    {
        if (
            ! config('services.vkontakte.client_id')
            || ! config('services.vkontakte.client_secret')
            || ! config('services.vkontakte.redirect')
        ) {
            return redirect()->route('login')->with(
                'vk_error',
                'Вход через VK не настроен: добавьте VKONTAKTE_* в .env и очистите кэш конфига (php artisan config:clear).'
            );
        }

        return Socialite::driver('vkontakte')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $sessionState = $request->session()->pull('state');
            $codeVerifier = $request->session()->pull('code_verifier');

            $params = $this->parseVkIdCallbackParams($request);

            if ($sessionState === null || $codeVerifier === null) {
                throw new InvalidStateException;
            }

            if ($params['state'] === '' || ! hash_equals($sessionState, $params['state'])) {
                throw new InvalidStateException;
            }

            if ($params['code'] === '') {
                throw new \RuntimeException('VK не вернул код авторизации (проверьте redirect_uri в кабинете VK ID).');
            }

            if ($params['device_id'] === '') {
                throw new \RuntimeException(
                    'VK не вернул device_id. Убедитесь, что приложение — VK ID и callback соответствует документации.',
                );
            }

            $redirectUri = config('services.vkontakte.redirect');
            $clientId = config('services.vkontakte.client_id');

            $tokenResp = Http::asForm()
                ->timeout(25)
                ->post('https://id.vk.ru/oauth2/auth', [
                    'grant_type' => 'authorization_code',
                    'code' => $params['code'],
                    'redirect_uri' => $redirectUri,
                    'client_id' => $clientId,
                    'device_id' => $params['device_id'],
                    'code_verifier' => $codeVerifier,
                    'state' => $params['state'],
                ]);

            if (! $tokenResp->successful()) {
                $body = $tokenResp->json();
                $msg = is_array($body) ? (string) ($body['error_description'] ?? $body['error'] ?? '') : '';
                $msg = $msg !== '' ? $msg : 'HTTP '.$tokenResp->status();

                throw new \RuntimeException('Обмен кода VK: '.$msg);
            }

            /** @var array<string, mixed> $body */
            $body = $tokenResp->json();
            $access = isset($body['access_token']) && is_string($body['access_token']) ? $body['access_token'] : null;

            if ($access === null || $access === '') {
                throw new \RuntimeException('VK не вернул access_token.');
            }

            $user = $this->vkIdUsers->authenticateWithAccessToken($access);
        } catch (InvalidStateException) {
            return redirect()
                ->route('login')
                ->with('vk_error', 'Сессия устарела — попробуйте войти через VK ещё раз.');
        } catch (\RuntimeException $e) {
            return redirect()->route('login')->with('vk_error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('login')
                ->with(
                    'vk_error',
                    'Не удалось войти через VK. Проверьте ключи приложения и Redirect URI в кабинете VK ID.',
                );
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('app'));
    }

    /**
     * VK ID редиректит с JSON в `payload`; старые сценарии могли отдавать поля в query.
     *
     * @return array{code: string, state: string, device_id: string}
     */
    private function parseVkIdCallbackParams(Request $request): array
    {
        $payloadRaw = $request->query('payload');

        if (is_string($payloadRaw) && $payloadRaw !== '') {
            $decoded = json_decode($payloadRaw, true);
            if (is_array($decoded)) {
                return [
                    'code' => (string) ($decoded['code'] ?? ''),
                    'state' => (string) ($decoded['state'] ?? ''),
                    'device_id' => (string) ($decoded['device_id'] ?? ''),
                ];
            }
        }

        return [
            'code' => (string) $request->query('code', ''),
            'state' => (string) $request->query('state', ''),
            'device_id' => (string) $request->query('device_id', ''),
        ];
    }

    /**
     * VK ID SDK: после VKID.Auth.exchangeCode на клиенте передаём access_token, сервер вызывает user_info.
     */
    public function sdkLogin(Request $request): JsonResponse
    {
        if (
            ! config('services.vkontakte.client_id')
            || ! config('services.vkontakte.redirect')
        ) {
            return response()->json([
                'message' => 'Вход через VK ID не настроен (нет VKONTAKTE_CLIENT_ID / REDIRECT_URI).',
            ], 503);
        }

        $validated = $request->validate([
            'access_token' => 'required|string|max:8192',
        ]);

        try {
            $user = $this->vkIdUsers->authenticateWithAccessToken($validated['access_token']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось подтвердить сессию VK. Попробуйте ещё раз или используйте другой способ входа.',
            ], 422);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('app'),
        ]);
    }
}
