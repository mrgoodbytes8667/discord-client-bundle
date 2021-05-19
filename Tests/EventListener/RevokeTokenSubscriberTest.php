<?php

namespace Bytes\DiscordBundle\Tests\EventListener;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\EventListener\RevokeTokenSubscriber;
use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Event\RevokeTokenEvent;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class RevokeTokenSubscriberTest
 * @package Bytes\DiscordBundle\Tests\EventListener
 */
class RevokeTokenSubscriberTest extends TestCase
{
    use TestDiscordFakerTrait;

    /**
     * @dataProvider provideTokenDetails
     * @param $identifier
     * @param $tokenSource
     * @throws TransportExceptionInterface
     */
    public function testOnRevokeToken($identifier, $tokenSource)
    {
        $token = Token::createFromAccessToken($this->faker->accessToken());
        $token->setIdentifier($identifier)
            ->setTokenSource($tokenSource);
        $event = RevokeTokenEvent::new($token);

        $userClient = $this->getMockBuilder(DiscordUserTokenClient::class)->disableOriginalConstructor()->getMock();

        $subscriber = new RevokeTokenSubscriber($userClient);

        $this->assertInstanceOf(RevokeTokenEvent::class, $subscriber->onRevokeToken($event));
    }

    /**
     * @return Generator
     */
    public function provideTokenDetails()
    {
        $this->setupFaker();
        foreach (array_merge(['DISCORD'], $this->faker->words(3)) as $identifier) {
            foreach ([TokenSource::user(), TokenSource::app(), TokenSource::id()] as $tokenSource) {
                yield ['identifier' => $identifier, 'tokenSource' => $tokenSource];
            }
        }
    }
}