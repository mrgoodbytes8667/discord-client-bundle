<?php


namespace Bytes\DiscordClientBundle\Resources\config;


use Bytes\DiscordClientBundle\Controller\CommandController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @param RoutingConfigurator $routes
 */
return function (RoutingConfigurator $routes) {
    //@Route("/add/{id}", name="bytesdiscordClientBundle_command_add", methods={"GET", "POST", "PATCH"}, format="json")
    $routes->add('bytesdiscordClientBundle_command_add', '/add/{guild}')
        ->controller([CommandController::class, 'add'])
        ->defaults(['guild' => null])
        ->methods(['GET', 'POST', 'PATCH'])
        ->format('json');

    //@Route("/{command}/delete/{guild}", name="bytesdiscordClientBundle_command_delete", methods={"GET", "DELETE"}, format="json")
    $routes->add('bytesdiscordClientBundle_command_delete', '/{command}/delete/{guild}')
        ->controller([CommandController::class, 'delete'])
        ->defaults(['guild' => null])
        ->methods(['GET', 'DELETE'])
        ->format('json');

    //@Route("/list/{guild}", name="bytesdiscordClientBundle_command_list", format="json")
    $routes->add('bytesdiscordClientBundle_command_list', '/list/{guild}')
        ->controller([CommandController::class, 'list'])
        ->defaults(['guild' => null])
        ->format('json');

    //@Route("/{command}/{guild}", name="bytesdiscordClientBundle_command_show", format="json")
    $routes->add('bytesdiscordClientBundle_command_show', '/{command}/{guild}')
        ->controller([CommandController::class, 'show'])
        ->defaults(['guild' => null])
        ->format('json');
};
