<?php


namespace Bytes\DiscordBundle\Security;


use Bytes\ResponseBundle\Token\Interfaces\AccessTokenInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DiscordOAuthAuthenticator extends \Bytes\ResponseBundle\Security\AbstractOAuthAuthenticator
{

    /**
     * @inheritDoc
     */
    protected function getUser(AccessTokenInterface $tokenResponse)
    {
        // TODO: Implement getUser() method.
    }
}