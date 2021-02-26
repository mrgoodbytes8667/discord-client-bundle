<?php


namespace Bytes\DiscordBundle\Controller;


use Bytes\DiscordBundle\Services\OAuth;
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
    public function __construct(Security $security, OAuth $oauth)
    {
        $this->security = $security;
        $this->oauth = $oauth;
    }

    /**
     * Route("/bot/redirect/{guildId}", name="bytes_discordbundle_oauth_bot_redirect")
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
            OAuthScopes::getBotSlashScopes(),
            $this->getUser()->getId(),
            'code',
            $guildId,
            !empty($guildId)
        ), Response::HTTP_FOUND);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return UserInterface|object|null
     *
     * @throws LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (null === $token = $this->security->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}