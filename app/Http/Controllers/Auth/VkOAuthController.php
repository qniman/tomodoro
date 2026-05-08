<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\VkIdUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class VkOAuthController extends Controller
{
    public function __construct(
        protected VkIdUserService $vkIdUsers,
    ) {}

    /**
     * Классический редирект OAuth (vkontakte Socialite) — альтернатива виджету SDK.
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
            $vk = Socialite::driver('vkontakte')->user();
            $user = $this->vkIdUsers->loginOrRegisterFromSocialite($vk);
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
                    'Не удалось войти через VK. Проверьте ключи приложения и Redirect URI.'
                );
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('app'));
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
