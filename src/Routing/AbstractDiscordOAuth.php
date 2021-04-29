<?php


namespace Bytes\DiscordBundle\Routing;


use BadMethodCallException;
use Bytes\DiscordBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\AbstractOAuth;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;
use Illuminate\Support\Arr;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

abstract class AbstractDiscordOAuth extends AbstractOAuth
{
    /**
     * @var string
     */
    protected static $promptKey = 'prompt';

    /**
     * @var string
     */
    protected static $baseAuthorizationCodeGrantURL = '';

    /**
     * @return UnicodeString
     */
    protected static function getBaseAuthorizationCodeGrantURL(): UnicodeString
    {
        return u(DiscordClientEndpoints::ENDPOINT_DISCORD_API)->ensureEnd('/')->append('oauth2/authorize')->ensureEnd('?');
    }

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
     * @inheritDoc
     */
    protected static function walkHydrateScopes(&$value, $key)
    {
        $value = (new OAuthScopes($value))->value;
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

    /**
     * Get the external URL begin the OAuth token exchange process
     * @param string|null $state
     * @param ...$options = ['prompt' => new OAuthPrompts()]
     * @return string
     */
    public function getAuthorizationUrl(?string $state = null, ...$options): string
    {
        $options = Push::createPush($options, OAuthPrompts::none(), 'prompt')
            ->value();
        return parent::getAuthorizationUrl($state, $options);
    }

    /**
     * Returns the $prompt argument for getAuthorizationCodeGrantURL() after normalization and validation
     * @param OAuthPromptInterface|string|bool|null $prompt
     * @param mixed ...$options
     * @return string|bool
     *
     * @throws BadMethodCallException
     */
    protected function normalizePrompt(bool|OAuthPromptInterface|string|null $prompt, ...$options)
    {
        if ($prompt instanceof OAuthPrompts) {
            return $prompt->prompt();
        } elseif (is_string($prompt) && OAuthPrompts::isValid($prompt)) {
            return OAuthPrompts::make($prompt)->prompt();
        } else {
            return OAuthPrompts::none()->prompt();
        }
    }
}