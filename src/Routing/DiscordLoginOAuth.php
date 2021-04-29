<?php


namespace Bytes\DiscordBundle\Routing;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;

/**
 * Class DiscordLoginOAuth
 * @package Bytes\DiscordBundle\Routing
 */
class DiscordLoginOAuth extends AbstractDiscordOAuth
{

    /**
     * @var string
     */
    protected static $endpoint = 'login';

    /**
     * @return Permissions[]
     */
    public function getDefaultPermissions(): array
    {
        return [];
    }

    /**
     * Returns the $prompt argument for getAuthorizationCodeGrantURL() after normalization and validation
     * @param OAuthPromptInterface|string|bool|null $prompt
     * @param mixed ...$options
     * @return string
     *
     * @throws \BadMethodCallException
     */
    protected function normalizePrompt(bool|OAuthPromptInterface|string|null $prompt, ...$options)
    {
        return OAuthPrompts::none()->prompt();
    }

    /**
     * @return OAuthScopes[]
     */
    protected function getDefaultScopes(): array
    {
        return [
            OAuthScopes::IDENTIFY(),
            OAuthScopes::CONNECTIONS(),
            OAuthScopes::GUILDS(),
        ];
    }
}