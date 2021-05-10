<?php


namespace Bytes\DiscordBundle\Subscriber;


use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Event\RevokeTokenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class RevokeTokenSubscriber
 * @package Bytes\DiscordBundle\Subscriber
 */
class RevokeTokenSubscriber implements EventSubscriberInterface
{
    /**
     * RevokeTokenSubscriber constructor.
     * @param DiscordUserTokenClient $discordUserTokenClient
     */
    public function __construct(private DiscordUserTokenClient $discordUserTokenClient)
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * The code must not depend on runtime state as it will only be called at compile time.
     * All logic depending on runtime state must be put into the individual methods handling the events.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            RevokeTokenEvent::class => ['onRevokeToken', 10],
        ];
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