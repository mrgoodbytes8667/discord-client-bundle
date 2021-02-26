<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\DiscordBundle\Controller\OAuthController;
use Bytes\DiscordBundle\Services\OAuth;

/**
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {

    $services = $container->services();

    $services->set('bytes_discord.oauth', OAuth::class)
        ->args([
            service('router.default'), // Symfony\Component\Routing\Generator\UrlGeneratorInterface
            '', // $config['client_id']
            '', // $config['redirects']
        ])
        ->alias(OAuth::class, 'bytes_discord.oauth')
        ->public();

    $services->set('bytes_discord.oauth_controller', OAuthController::class)
        ->args([
            service('security.helper'), // Symfony\Component\Security\Core\Security
            service('bytes_discord.oauth'), // Bytes\DiscordBundle\Services\OAuth
            '', // $config['user']
        ])
        ->alias(OAuthController::class, 'bytes_discord.oauth_controller')
        ->public();

};