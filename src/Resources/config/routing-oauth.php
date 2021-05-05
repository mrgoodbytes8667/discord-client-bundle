<?php


namespace Bytes\DiscordBundle\Resources\config;


use Bytes\DiscordBundle\Controller\OAuthController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @param RoutingConfigurator $routes
 */
return function (RoutingConfigurator $routes) {
    //@Route("/bot/redirect/{guildId}", name="bytesdiscordbundle_oauth_bot_redirect")
    $routes->add('bytesdiscordbundle_oauth_bot_redirect', '/bot/redirect/{guildId}')
        ->controller([OAuthController::class, 'botRedirect'])
        ->defaults(['guildId' => null]);

    //@Route("/user/redirect", name="bytesdiscordbundle_oauth_user_redirect")
    $routes->add('bytesdiscordbundle_oauth_user_redirect', '/user/redirect')
        ->controller([OAuthController::class, 'userRedirect']);

    //@Route("/login", name="bytesdiscordbundle_oauth_login_redirect")
    $routes->add('bytesdiscordbundle_oauth_login_redirect', '/login')
        ->controller([OAuthController::class, 'loginRedirect']);
};
