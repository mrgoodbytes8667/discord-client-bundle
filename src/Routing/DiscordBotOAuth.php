<?php


namespace Bytes\DiscordBundle\Routing;


use BadMethodCallException;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;

/**
 * Class DiscordBotOAuth
 * @package Bytes\DiscordBundle\Routing
 */
class DiscordBotOAuth extends AbstractDiscordBotOAuth
{

    /**
     * @var string
     */
    protected static $endpoint = 'bot';

    /**
     * @return Permissions[]
     */
    public function getDefaultPermissions(): array
    {
        return [
            Permissions::ADD_REACTIONS(),
            Permissions::VIEW_CHANNEL(),
            Permissions::SEND_MESSAGES(),
            Permissions::MANAGE_MESSAGES(),
            Permissions::READ_MESSAGE_HISTORY(),
            Permissions::EMBED_LINKS(),
            Permissions::USE_EXTERNAL_EMOJIS(),
            Permissions::MANAGE_ROLES(),
        ];
    }

    /**
     * @return OAuthScopes[]
     */
    protected function getDefaultScopes(): array
    {
        return OAuthScopes::getBotScopes();
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