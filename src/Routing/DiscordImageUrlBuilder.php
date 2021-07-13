<?php


namespace Bytes\DiscordClientBundle\Routing;


use Bytes\DiscordResponseBundle\Objects\User;
use function Symfony\Component\String\u;

trigger_deprecation('mrgoodbytes8667/discord-client-bundle', '0.1.3', 'Using "Bytes\DiscordClientBundle\Routing\DiscordImageUrlBuilder" is deprecated, use "Bytes\DiscordResponseBundle\Routing\DiscordImageUrlBuilder" instead.');

/**
 * Class DiscordImageUrlBuilder
 * @package Bytes\DiscordClientBundle\Routing
 *
 * @deprecated Since 0.1.3, use "Bytes\DiscordResponseBundle\Routing\DiscordImageUrlBuilder" instead
 */
class DiscordImageUrlBuilder
{
    /**
     * @param User $user
     * @return string|null
     */
    public static function getAvatarUrl(User $user): ?string
    {
        $url = u(implode('/', [
            'https://cdn.discordapp.com/avatars',
            $user->getId(),
            $user->getAvatar()
        ]));

        if(u($user->getAvatar())->startsWith('a_')) {
            $url .= '.gif';
        } else {
            $url .= '.png';
        }

        return $url;
    }
}