<?php


namespace Bytes\DiscordClientBundle\Routing;


use BadMethodCallException;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;

/**
 * Class DiscordUserOAuth
 * @package Bytes\DiscordClientBundle\Routing
 */
class DiscordUserOAuth extends AbstractDiscordOAuth
{
    /**
     * @var string
     */
    protected static $endpoint = 'user';

    /**
     * @return OAuthScopes[]
     */
    protected function getDefaultScopes(): array
    {
        return OAuthScopes::getUserScopes();
    }

    /**
     * Returns the $prompt argument for getAuthorizationCodeGrantURL() after normalization and validation
     * @param OAuthPromptInterface|string|bool|null $prompt
     * @param mixed ...$options
     * @return string
     *
     * @throws BadMethodCallException
     */
    protected function normalizePrompt(bool|OAuthPromptInterface|string|null $prompt, ...$options)
    {
        return OAuthPrompts::CONSENT->prompt();
    }

    /**
     * Return the client name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'DISCORD-USER';
    }
}