<?php


namespace Bytes\DiscordBundle\Routing;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Illuminate\Support\Arr;

abstract class AbstractDiscordBotOAuth extends AbstractDiscordOAuth
{
    /**
     * Cached normalized permissions list
     * @var array
     */
    private $permissions = [];

    /**
     * @var array
     */
    private $defaultPermissions;

    public function __construct(?string $clientId, array $config, array $options = [])
    {
        parent::__construct($clientId, $config, $options);
        $this->defaultPermissions = $this->getDefaultPermissions();
    }

    /**
     * @return array
     */
    abstract public function getDefaultPermissions(): array;

    public static function hydratePermissions(array $permissions)
    {
        array_walk($permissions, array('self', 'walkHydratePermissions'));
        return $permissions;
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
     * @param Push $query
     * @param ...$options
     * @return Push
     */
    protected function appendToAuthorizationCodeGrantURLQuery(Push $query, ...$options): Push
    {
        $permissions = $this->permissions ?? $this->normalizePermissions($options['permissions'] ?? $this->getDefaultPermissions());
        $this->permissions = $permissions;

        $query = $query->push(value: $this->permissions, key: 'permissions');

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
}