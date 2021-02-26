<?php


namespace Bytes\DiscordBundle\Services;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OAuth
 * @package Bytes\DiscordBundle\Services
 */
class OAuth
{
    /**
     * @var string
     */
    private $discordClientId;

    /**
     * @var string
     */
    private $userOAuthRedirect;

    /**
     * @var string
     */
    private $botOAuthRedirect;

    /**
     * @var string
     */
    private $loginOAuthRedirect;

    /**
     * @var string
     */
    private $slashOAuthRedirect;

    /**
     * OAuth constructor.
     * @param UrlGeneratorInterface|null $urlGenerator
     * @param string $discordClientId
     * @param array $redirects = ['bot' => ['method' => '', 'route_name' => '', 'url' => ''], 'user' => ['method' => '', 'route_name' => '', 'url' => ''], 'slash' => ['method' => '', 'route_name' => '', 'url' => ''], 'login' => ['method' => '', 'route_name' => '', 'url' => '']]
     */
    public function __construct(?UrlGeneratorInterface $urlGenerator, string $discordClientId, array $redirects)
    {
        $this->discordClientId = $discordClientId;

        $this->userOAuthRedirect = $this->setupRedirect($redirects['user'], $urlGenerator);
        $this->botOAuthRedirect = $this->setupRedirect($redirects['bot'], $urlGenerator);
        $this->loginOAuthRedirect = $this->setupRedirect($redirects['login'], $urlGenerator);
        $this->slashOAuthRedirect = $this->setupRedirect($redirects['slash'], $urlGenerator);
    }

    /**
     * @param array $redirect = ['method' => ['route_name','url'][$any], 'route_name' => '', 'url' => '']
     * @param UrlGeneratorInterface|null $urlGenerator
     * @return string
     */
    protected function setupRedirect(array $redirect, ?UrlGeneratorInterface $urlGenerator)
    {
        switch ($redirect['method']) {
            case 'route_name':
                if (empty($urlGenerator)) {
                    throw new \InvalidArgumentException('URLGeneratorInterface cannot be null when a route name is passed');
                }
                return $urlGenerator->generate($redirect['route_name'], [], UrlGeneratorInterface::ABSOLUTE_URL);
                break;
            case 'url':
                return $redirect['url'];
                break;
            default:
                throw new \InvalidArgumentException("Param 'redirect' must be one of 'route_name' or 'url'");
                break;
        }
    }

    /**
     * @param array $permissions = Permissions::all()
     * @param string $redirect
     * @param array $scopes = OAuthScopes::all()
     * @param string $state
     * @param string $responseType = ['code']
     * @param string|null $guildId
     * @param bool|null $disableGuildSelect
     * @param null $prompt = [OAuthPrompts::none(),OAuthPrompts::consent()]
     *
     * @return string
     */
    public function getAuthorizationCodeGrantURL(array $permissions, string $redirect, array $scopes, string $state, string $responseType = 'code', ?string $guildId = null, ?bool $disableGuildSelect = null, $prompt = null)
    {
        if (empty($prompt)) {
            $prompt = OAuthPrompts::consent();
        }
        if (is_string($prompt)) {
            $prompt = new OAuthPrompts($prompt);
        }
        $query = [
            'client_id' => $this->discordClientId,
            'permissions' => Permissions::getFlags($permissions),
            'redirect_uri' => $redirect,
            'response_type' => $responseType,
            'scope' => OAuthScopes::buildOAuthString($scopes),
            'state' => $state,
            'prompt' => $prompt->value,
        ];
        if (!empty($guildId)) {
            $query['guild_id'] = $guildId;
            if ($disableGuildSelect === true) {
                $query['disable_guild_select'] = 'true';
            }
        }
        return 'https://discord.com/api/oauth2/authorize?' . http_build_query($query);
    }

    /**
     * @return string
     */
    public function getUserOAuthRedirect(): string
    {
        return $this->userOAuthRedirect;
    }

    /**
     * @return string
     */
    public function getBotOAuthRedirect(): string
    {
        return $this->botOAuthRedirect;
    }

    /**
     * @return string
     */
    public function getLoginOAuthRedirect(): string
    {
        return $this->loginOAuthRedirect;
    }

    /**
     * @return string
     */
    public function getSlashOAuthRedirect(): string
    {
        return $this->slashOAuthRedirect;
    }
}