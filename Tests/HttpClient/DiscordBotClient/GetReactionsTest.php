<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetReactionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class GetReactionsTest extends TestDiscordBotClientCase
{
    use ReactionsProviderTrait;

    /**
     * @dataProvider provideValidGetReaction
     * @param $message
     * @param $channel
     * @param $emoji
     * @param $before
     * @param $after
     * @param $limit
     */
    public function testGetReactions($message, $channel, $emoji, $before, $after, $limit)
    {
        $client = $this->setupClient(MockClient::requests(MockJsonResponse::makeFixture('HttpClient/get-reactions-success.json')));

        $response = $client->getReactions($message, $emoji, $channel, $before, $after, $limit);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-reactions-success.json'));
    }

    /**
     * @return Generator
     */
    public function provideValidGetReaction(): Generator
    {
        $this->setupFaker();

        foreach ($this->provideValidCreateReaction() as $generator) {
            foreach ([$this->faker->userId(), null] as $before) {
                foreach ([$this->faker->userId(), null] as $after) {
                    foreach ([-1, 0, 1, 10, 50, 90, 99, 100, 101, null] as $limit) {
                        yield ['message' => $generator['message'], 'channel' => $generator['channel'], 'emoji' => $generator['emoji'], 'before' => $before, 'after' => $after, 'limit' => $limit];
                    }
                }
            }

        }
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testGetReactionsBadGuildArgument($message, $channel)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getReactions($message, self::getRandomEmoji(), $channel);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetReactionsJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->getReactions('123', self::getRandomEmoji(), '456');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetReactionsFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getReactions('123', self::getRandomEmoji(), '456');
    }
}

