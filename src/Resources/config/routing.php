<?php


namespace Bytes\DiscordBundle\Resources\config;


use Bytes\DiscordBundle\Controller\OAuthController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @param RoutingConfigurator $routes
 */
return function (RoutingConfigurator $routes) {
    //@Route("/bot/redirect/{guildId}", name="bytes_discordbundle_oauth_bot_redirect")
    $routes->add('bytes_discordbundle_oauth_bot_redirect', '/bot/redirect/{guildId}')
        ->controller([OAuthController::class, 'botRedirect']);
};
