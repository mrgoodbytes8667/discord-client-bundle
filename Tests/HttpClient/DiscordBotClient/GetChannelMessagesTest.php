<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ChannelIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetChannelMessagesTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class GetChannelMessagesTest extends TestDiscordBotClientCase
{
    use MessageProviderTrait;
    
    /**
     * @dataProvider provideValidChannelMessages
     */
    public function testGetChannelMessages($channel, $filter, $message, $limit)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-messages-success.json'),
        ]));

        $response = $client->getChannelMessages($channel, $filter, $message, $limit);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-messages-success.json'));
    }

    public function provideValidChannelMessages()
    {
        $this->setupFaker();

        foreach ($this->provideValidChannelMessagesInternal() as $cm) {
            foreach ([-1, 0, 1, 10, 50, 90, 99, 100, 101, null] as $limit) {
                foreach ($this->faker->filter() as $filter) {
                    yield ['channel' => $cm['channel'], 'filter' => empty($cm['message']) ? null : $filter, 'message' => $cm['message'], 'limit' => $limit];
                }
            }
        }

    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelMessagesFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannelMessages('245963893292923965');
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testGetChannelMessagesBadChannelArgument($message, $channel)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannelMessages($channel);
    }
}

