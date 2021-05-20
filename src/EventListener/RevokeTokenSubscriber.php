<?php


namespace Bytes\DiscordClientBundle\EventListener;


use Bytes\DiscordClientBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Event\RevokeTokenEvent;
use Bytes\ResponseBundle\EventListener\AbstractRevokeTokenSubscriber;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class RevokeTokenSubscriber
 * @package Bytes\DiscordClientBundle\EventListener
 */
class RevokeTokenSubscriber extends AbstractRevokeTokenSubscriber
{
    /**
     * RevokeTokenSubscriber constructor.
     * @param DiscordUserTokenClient $discordUserTokenClient
     */
    public function __construct(private DiscordUserTokenClient $discordUserTokenClient)
    {
    }

    /**
     * @param RevokeTokenEvent $event
     * @return RevokeTokenEvent
     * @throws TransportExceptionInterface
     */
    public function onRevokeToken(RevokeTokenEvent $event): RevokeTokenEvent
    {
        $token = $event->getToken();
        if ($token->getIdentifier() == 'DISCORD' && $token->getTokenSource()->equals(TokenSource::user())) {
            $this->discordUserTokenClient->revokeToken($token);
        }

        return $event;
    }
}