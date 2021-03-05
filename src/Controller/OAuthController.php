<?php


namespace Bytes\DiscordBundle\Controller;


use Bytes\DiscordBundle\Services\OAuth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

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
     * OAuthController constructor.
     * @param OAuth $oauth
     */
    public function __construct(OAuth $oauth)
    {
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
        return new RedirectResponse($this->oauth->getBotAuthorizationUrl($guildId), Response::HTTP_FOUND);
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
        return new RedirectResponse($this->oauth->getSlashAuthorizationUrl($guildId), Response::HTTP_FOUND);
    }

    /**
     * Route("/user/redirect", name="bytesdiscordbundle_oauth_user_redirect")
     *
     * @return RedirectResponse
     */
    public function userRedirect()
    {
        return new RedirectResponse($this->oauth->getUserAuthorizationUrl(), Response::HTTP_FOUND);
    }

    /**
     * Route("/login", name="bytesdiscordbundle_oauth_login_redirect")
     *
     * @return RedirectResponse
     */
    public function routeOAuthLogin()
    {
        return new RedirectResponse($this->oauth->getOAuthLoginUrl(), Response::HTTP_FOUND);
    }
}