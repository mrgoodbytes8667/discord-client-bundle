<?php


namespace Bytes\DiscordClientBundle\Routing;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;
use Illuminate\Support\Arr;

/**
 * Class DiscordBotOAuth
 * @package Bytes\DiscordClientBundle\Routing
 */
class DiscordBotOAuth extends AbstractDiscordOAuth
{
    /**
     * @var string
     */
    protected static $endpoint = 'bot';

    /**
     * Cached normalized permissions list
     * @var array
     */
    private $permissions = [];

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

    public static function hydratePermissions(array $permissions)
    {
        array_walk($permissions, array('self', 'walkHydratePermissions'));
        return $permissions;
    }

    /**
     * @return OAuthScopes[]
     */
    protected function getDefaultScopes(): array
    {
        return array_merge(OAuthScopes::getBotScopes(), OAuthScopes::getSlashScopes());
    }

    /**
     * @param $value
     * @param $key
     */
    protected static function walkHydratePermissions(&$value, $key)
    {
        $value = new Permissions($value);
    }

    /**
     * @param Push $query
     * @param ...$options
     * @return Push
     */
    protected function appendToAuthorizationCodeGrantURLQuery(Push $query, ...$options): Push
    {
        $permissions = $this->permissions ?: $this->normalizePermissions($options['permissions'] ?? $this->getDefaultPermissions());
        $this->permissions = $permissions;

        $query = $query->push(value: Permissions::getFlags($this->permissions), key: 'permissions')
            ->push(value: $options['guildId'] ?? null, key: 'guildId')
            ->push(value: ($options['disableGuildSelect'] ?? !empty($options['guildId'])) ? 'true' : 'false', key: 'disableGuildSelect');

        return parent::appendToAuthorizationCodeGrantURLQuery($query, $options);
    }

    /**
     * Takes the default permissions list and adds/removes any permissions coming from the config
     * @param array $permissions
     * @return array
     */
    protected function normalizePermissions(array $permissions)
    {
        if (array_key_exists('add', $this->config[static::$endpoint]['permissions'])) {
            $add = $this->config[static::$endpoint]['permissions']['add'];
            if (count($add) > 0) {
                array_walk($add, array('self', 'walkHydratePermissions'));
                $permissions = array_unique(array_merge($permissions, $add));
            }
        }

        if (array_key_exists('remove', $this->config[static::$endpoint]['permissions'])) {
            $remove = $this->config[static::$endpoint]['permissions']['remove'];
            if (count($remove) > 0) {
                array_walk($remove, array('self', 'walkHydratePermissions'));

                $permissions = Arr::where($permissions, function ($value, $key) use ($remove) {
                    return !in_array($value, $remove);
                });
            }
        }

        return $permissions;
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

    /**
     * Return the client name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'DISCORD-BOT';
    }
}