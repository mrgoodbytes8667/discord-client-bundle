<?php


namespace Bytes\DiscordBundle\Controller;


use Bytes\DiscordBundle\Services\OAuth;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use LogicException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthController
 * @package Bytes\DiscordBundle\Controller
 */
class OAuthController
{
    /**
     * @var OAuth
     */
    private $oauth;

    /**
     * @var Security
     */
    private $security;

    /**
     * OAuthController constructor.
     * @param Security $security
     * @param OAuth $oauth
     */
    public function __construct(Security $security, OAuth $oauth, bool $user)
    {
        if($user) {
            $this->security = $security;
        }
        $this->oauth = $oauth;
    }

    /**
     * Route("/bot/redirect/{guildId}", name="bytesdiscordbundle_oauth_bot_redirect")
     *
     * @param string|null $guildId
     *
     * @return RedirectResponse
     */
    public function botRedirect(string $guildId = null)
    {
        return new RedirectResponse($this->oauth->getAuthorizationCodeGrantURL(
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
            $this->oauth->getBotOAuthRedirect(),
            OAuthScopes::getBotScopes(),
            $this->getState('botRedirect'),
            'code',
            $guildId,
            !empty($guildId)
        ), Response::HTTP_FOUND);
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
        if(empty($this->security)) {
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
     * @param string $route
     * @return string
     */
    protected function getState(string $route)
    {
        switch ($route)
        {
            case 'routeOAuthLogin':
                return 'state';
                break;
            default:
                $user = '';
                if(!empty($this->security)) {
                    $user = $this->getUser()->getId();
                }
                return $user;
                break;
        }
    }

    /**
     * Route("/slash/redirect/{guildId}", name="bytesdiscordbundle_oauth_slash_redirect")
     *
     * @param string|null $guildId
     *
     * @return RedirectResponse
     */
    public function slashRedirect(string $guildId = null)
    {
        return new RedirectResponse($this->oauth->getAuthorizationCodeGrantURL(
            [],
            $this->oauth->getSlashOAuthRedirect(),
            OAuthScopes::getSlashScopes(),
            $this->getState('slashRedirect'),
            'code',
            $guildId,
            !empty($guildId)
        ), Response::HTTP_FOUND);
    }

    /**
     * Route("/user/redirect", name="bytesdiscordbundle_oauth_user_redirect")
     *
     * @return RedirectResponse
     */
    public function userRedirect()
    {
        return new RedirectResponse($this->oauth->getAuthorizationCodeGrantURL(
            [],
            $this->oauth->getUserOAuthRedirect(),
            OAuthScopes::getUserScopes(),
            $this->getState('userRedirect')));
    }

    /**
     * Route("/login", name="bytesdiscordbundle_oauth_login_redirect")
     *
     * @return RedirectResponse
     */
    public function routeOAuthLogin()
    {
        return new RedirectResponse($this->oauth->getAuthorizationCodeGrantURL(
            [],
            $this->oauth->getLoginOAuthRedirect(),
            [
                OAuthScopes::IDENTIFY(),
                OAuthScopes::CONNECTIONS(),
                OAuthScopes::GUILDS(),
            ],
            $this->getState('routeOAuthLogin'), 'code', null, null, OAuthPrompts::none()));
    }
}