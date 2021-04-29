<?php


namespace Bytes\DiscordBundle\Routing;


use BadMethodCallException;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;

/**
 * Class DiscordSlashOAuth
 * @package Bytes\DiscordBundle\Routing
 */
class DiscordSlashOAuth extends AbstractDiscordBotOAuth
{

    /**
     * @var string
     */
    protected static $endpoint = 'slash';

    /**
     * @return Permissions[]
     */
    public function getDefaultPermissions(): array
    {
        return [];
    }

    /**
     * @return OAuthScopes[]
     */
    protected function getDefaultScopes(): array
    {
        return OAuthScopes::getSlashScopes();
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
        return OAuthPrompts::consent()->prompt();
    }
}