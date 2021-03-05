<?php


namespace Bytes\DiscordBundle\Services;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var Security
     */
    private $security;

    /**
     * OAuth constructor.
     * @param Security $security
     * @param UrlGeneratorInterface|null $urlGenerator
     * @param string $discordClientId
     * @param array $redirects = ['bot' => ['method' => '', 'route_name' => '', 'url' => ''], 'user' => ['method' => '', 'route_name' => '', 'url' => ''], 'slash' => ['method' => '', 'route_name' => '', 'url' => ''], 'login' => ['method' => '', 'route_name' => '', 'url' => '']]
     * @param bool $user
     */
    public function __construct(Security $security, ?UrlGeneratorInterface $urlGenerator, string $discordClientId, array $redirects, bool $user)
    {
        $this->discordClientId = $discordClientId;

        $this->userOAuthRedirect = $this->setupRedirect($redirects['user'], $urlGenerator);
        $this->botOAuthRedirect = $this->setupRedirect($redirects['bot'], $urlGenerator);
        $this->loginOAuthRedirect = $this->setupRedirect($redirects['login'], $urlGenerator);
        $this->slashOAuthRedirect = $this->setupRedirect($redirects['slash'], $urlGenerator);

        if ($user) {
            $this->security = $security;
        }
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
                    throw new InvalidArgumentException('URLGeneratorInterface cannot be null when a route name is passed');
                }
                return $urlGenerator->generate($redirect['route_name'], [], UrlGeneratorInterface::ABSOLUTE_URL);
                break;
            case 'url':
                return $redirect['url'];
                break;
            default:
                throw new InvalidArgumentException("Param 'redirect' must be one of 'route_name' or 'url'");
                break;
        }
    }

    /**
     * @param string|null $guildId
     * @param string|null $state
     * @return string
     */
    public function getBotAuthorizationUrl(string $guildId = null, ?string $state = null): string
    {
        return $this->getAuthorizationCodeGrantURL(
            [
                Permissions::ADD_REACTIONS(),
                Permissions::VIEW_CHANNEL(),
                Permissions::SEND_MESSAGES(),
                Permissions::MANAGE_MESSAGES(),
                Permissions::READ_MESSAGE_HISTORY(),
                Permissions::EMBED_LINKS(),
                Permissions::USE_EXTERNAL_EMOJIS(),
                Permissions::MANAGE_ROLES(),
            ],
            $this->getBotOAuthRedirect(),
            OAuthScopes::getBotScopes(),
            $state ?? $this->getState('botRedirect'),
            'code',
            $guildId,
            !empty($guildId)
        );
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
    public function getBotOAuthRedirect(): string
    {
        return $this->botOAuthRedirect;
    }

    /**
     * @param string $route
     * @return string
     */
    protected function getState(string $route)
    {
        switch ($route) {
            case 'routeOAuthLogin':
                return 'state';
                break;
            default:
                $user = '';
                if (!empty($this->security)) {
                    $user = $this->getUser()->getId();
                }
                return $user;
                break;
        }
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return UserInterface|null
     *
     * @throws LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (empty($this->security)) {
            return null;
        }

        if (null === $token = $this->security->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * @param string|null $guildId
     * @param string|null $state
     * @return string
     */
    public function getSlashAuthorizationUrl(string $guildId = null, ?string $state = null): string
    {
        return $this->getAuthorizationCodeGrantURL(
            [],
            $this->getSlashOAuthRedirect(),
            OAuthScopes::getSlashScopes(),
            $state ?? $this->getState('slashRedirect'),
            'code',
            $guildId,
            !empty($guildId)
        );
    }

    /**
     * @return string
     */
    public function getSlashOAuthRedirect(): string
    {
        return $this->slashOAuthRedirect;
    }

    /**
     * @param string|null $state
     * @return string
     */
    public function getUserAuthorizationUrl(?string $state = null): string
    {
        return $this->getAuthorizationCodeGrantURL(
            [],
            $this->getUserOAuthRedirect(),
            OAuthScopes::getUserScopes(),
            $state ?? $this->getState('userRedirect'));
    }

    /**
     * @return string
     */
    public function getUserOAuthRedirect(): string
    {
        return $this->userOAuthRedirect;
    }

    /**
     * @param string|null $state
     * @return string
     */
    public function getOAuthLoginUrl(?string $state = null): string
    {
        return $this->getAuthorizationCodeGrantURL(
            [],
            $this->getLoginOAuthRedirect(),
            [
                OAuthScopes::IDENTIFY(),
                OAuthScopes::CONNECTIONS(),
                OAuthScopes::GUILDS(),
            ],
            $state ?? $this->getState('routeOAuthLogin'), 'code', null, null, OAuthPrompts::none());
    }

    /**
     * @return string
     */
    public function getLoginOAuthRedirect(): string
    {
        return $this->loginOAuthRedirect;
    }
}