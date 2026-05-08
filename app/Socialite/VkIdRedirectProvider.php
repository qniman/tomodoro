<?php

namespace App\Socialite;

use SocialiteProviders\VKontakte\Provider as VkontakteProvider;

/**
 * Редирект на VK ID (id.vk.ru) с PKCE. Обмен кода на токен делается вручную в callback VkOAuthController,
 * потому что VK возвращает code/state/device_id внутри query-параметра `payload` (JSON), а не отдельными полями.
 */
class VkIdRedirectProvider extends VkontakteProvider
{
    /** @see https://datatracker.ietf.org/doc/html/rfc7636 */
    protected $usesPKCE = true;

    /** VK ID: области через пробел. */
    protected $scopeSeparator = ' ';

    /** @see https://id.vk.com/about/business/go/docs/ru/vkid/latest/vk-id/connection/work-with-user-info/scopes */
    protected $scopes = ['vkid.personal_info', 'email'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://id.vk.ru/authorize', $state);
    }
}
