<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ChannelIdInterface;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CrosspostMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class CrosspostMessageTest extends TestDiscordBotClientCase
{
    use MessageProviderTrait;

    /**
     * @dataProvider provideValidCrosspost
     * @param $channel
     * @param $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCrosspostMessage($channel, $message)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/crosspost-message-success.json'),
        ]));

        $response = $client->crosspostMessage($message, $channel);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/crosspost-message-success.json'));
    }

    /**
     * @dataProvider provideInvalidChannelValidContent
     * @param $channel
     * @param $content
     * @param $tts
     */
    public function testCrosspostMessageBadChannelArgument($channel, $content, $tts)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->crosspostMessage('456', $channel);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testCrosspostMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->crosspostMessage('123', '456');
    }

    /**
     * @return Generator
     * @internal
     */
    public function provideValidCrosspost()
    {
        $message = new Message();
        $message->setId('123');
        $message->setChannelID('456');

        yield ['channel' => $message, 'message' => $message];
        yield ['channel' => null, 'message' => $message];

        $message = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $message->method('getId')
            ->willReturn('230858112993375816');

        $channel = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $channel->method('getId')
            ->willReturn('230858112993375816');
        yield ['channel' => $channel, 'message' => $message];

        $channel = $this
            ->getMockBuilder(ChannelIdInterface::class)
            ->getMock();
        $channel->method('getChannelId')
            ->willReturn('230858112993375816');
        yield ['channel' => $channel, 'message' => $message];

        yield ['channel' => '456', 'message' => '123'];
    }
}