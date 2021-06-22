<?php


namespace Bytes\DiscordClientBundle\Routing;


use Bytes\DiscordResponseBundle\Objects\User;
use function Symfony\Component\String\u;

/**
 * Class DiscordImageUrlBuilder
 * @package Bytes\DiscordClientBundle\Routing
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