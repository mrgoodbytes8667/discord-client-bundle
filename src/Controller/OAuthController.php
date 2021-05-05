<?php


namespace Bytes\DiscordBundle\Controller;


use Bytes\DiscordBundle\Services\OAuth;
use Bytes\ResponseBundle\Routing\OAuthInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OAuthController
 * @package Bytes\DiscordBundle\Controller
 */
class OAuthController
{
    /**
     * OAuthController constructor.
     * @param OAuthInterface $discordBotOAuth
     * @param OAuthInterface $discordLoginOAuth
     * @param OAuthInterface $discordUserOAuth
     */
    public function __construct(private OAuthInterface $discordBotOAuth, private OAuthInterface $discordLoginOAuth, private OAuthInterface $discordUserOAuth)
    {
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
        return new RedirectResponse($this->discordBotOAuth->getAuthorizationUrl($guildId), Response::HTTP_FOUND);
    }

    /**
     * Route("/user/redirect", name="bytesdiscordbundle_oauth_user_redirect")
     *
     * @return RedirectResponse
     */
    public function userRedirect()
    {
        return new RedirectResponse($this->discordUserOAuth->getAuthorizationUrl(), Response::HTTP_FOUND);
    }

    /**
     * Route("/login", name="bytesdiscordbundle_oauth_login_redirect")
     *
     * @return RedirectResponse
     */
    public function loginRedirect()
    {
        return new RedirectResponse($this->discordLoginOAuth->getAuthorizationUrl(), Response::HTTP_FOUND);
    }
}