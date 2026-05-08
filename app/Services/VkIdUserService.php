<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteContractUser;

class VkIdUserService
{
    /**
     * Проверенный профиль через VK ID OAuth2 user_info (https://id.vk.ru/oauth2/user_info).
     */
    public function authenticateWithAccessToken(string $accessToken): User
    {
        $clientId = config('services.vkontakte.client_id');

        if (! $clientId) {
            throw new \RuntimeException('VK client_id is not configured.');
        }

        $response = Http::asForm()
            ->timeout(20)
            ->post('https://id.vk.ru/oauth2/user_info', [
                'client_id' => $clientId,
                'access_token' => $accessToken,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'VK user_info HTTP '.$response->status().': '.Str::limit($response->body(), 200),
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        /** @var array<string, mixed>|null $payload */
        $payload = isset($json['user']) && is_array($json['user']) ? $json['user'] : null;

        if ($payload === null || ! isset($payload['user_id'])) {
            if (isset($json['error'])) {
                $err = is_scalar($json['error']) ? (string) $json['error'] : '';
                $desc = isset($json['error_description']) && is_scalar($json['error_description'])
                    ? (string) $json['error_description']
                    : '';
                throw new \RuntimeException('VK user_info error: '.($desc !== '' ? $desc : $err));
            }

            throw new \RuntimeException('VK user_info: invalid payload.');
        }

        $vkId = (string) $payload['user_id'];

        $first = isset($payload['first_name']) && is_string($payload['first_name']) ? $payload['first_name'] : '';
        $last = isset($payload['last_name']) && is_string($payload['last_name']) ? $payload['last_name'] : '';
        $name = trim("{$first} {$last}");

        $emailRaw = isset($payload['email']) ? $payload['email'] : null;
        $email = is_string($emailRaw) && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : null;

        return $this->loginOrRegister($vkId, $email, $name !== '' ? $name : null);
    }

    /**
     * Данные из Laravel Socialite (legacy OAuth через vkontakte драйвер).
     */
    public function loginOrRegisterFromSocialite(SocialiteContractUser $vk): User
    {
        $vkId = (string) $vk->getId();

        $emailRaw = $vk->getEmail();
        $email = is_string($emailRaw) && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : null;

        $name = trim((string) $vk->getName());
        if ($name === '') {
            $nick = $vk->getNickname();
            $name = is_string($nick) && $nick !== '' ? $nick : 'Пользователь VK';
        }

        return $this->loginOrRegister($vkId, $email, $name);
    }

    public function loginOrRegister(string $vkId, ?string $email, ?string $name): User
    {
        $name = $name !== null && trim($name) !== '' ? trim($name) : 'Пользователь VK';

        $user = User::query()->where('vk_id', $vkId)->first();

        if ($user === null && $email !== null) {
            $byEmail = User::query()->where('email', $email)->first();
            if ($byEmail !== null) {
                if ($byEmail->vk_id !== null && $byEmail->vk_id !== $vkId) {
                    throw new \RuntimeException(
                        'Этот email уже привязан к другому аккаунту VK.',
                    );
                }
                $byEmail->forceFill(['vk_id' => $vkId])->save();
                $user = $byEmail->fresh();
            }
        }

        if ($user === null) {
            $chosenEmail = $email ?? $this->placeholderEmail($vkId);

            while (User::query()->where('email', $chosenEmail)->exists()) {
                $chosenEmail = $this->placeholderEmail($vkId.'_'.Str::lower(Str::random(4)));
            }

            return User::create([
                'name' => $name,
                'email' => $chosenEmail,
                'password' => Hash::make(Str::random(48)),
                'vk_id' => $vkId,
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    /** Синтетический email, если VK не отдал почту. */
    public function placeholderEmail(string $vkId): string
    {
        $domain = (string) config('services.vkontakte.placeholder_email_domain', 'oauth.local');
        $slug = preg_replace('/[^a-zA-Z0-9]/', '', $vkId) ?: 'user';

        return "vk_oauth_{$slug}@{$domain}";
    }
}
