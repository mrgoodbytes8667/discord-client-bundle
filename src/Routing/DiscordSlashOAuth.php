<?php


namespace Bytes\DiscordBundle\Routing;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;

/**
 * Class DiscordSlashOAuth
 * @package Bytes\DiscordBundle\Routing
 */
class DiscordSlashOAuth extends AbstractDiscordOAuth
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
     * Get the external URL begin the OAuth token exchange process
     * @param string|null $state
     * @param ...$options = ['prompt' => new OAuthPrompts(), 'guildId' => '', 'disableGuildSelect' => true]
     * @return string
     */
    public function getAuthorizationUrl(?string $state = null, ...$options): string
    {
        $options = Push::createPush($options, OAuthPrompts::none(), 'prompt')
            ->push(value: $options['guildId'], key: 'guildId')
            ->push(value: $options['disableGuildSelect'] ?? !empty($options['guildId']), key: 'disableGuildSelect')
            ->value();
        return parent::getAuthorizationUrl($state, $options);
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
        return OAuthPrompts::consent()->prompt();
    }
}