<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteMessageTest extends TestDiscordBotClientCase
{
    use MessageProviderTrait;

    /**
     * @dataProvider provideValidDeleteMessages
     */
    public function testDeleteMessage($message, $channel)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteMessage($message, $channel);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testDeleteMessageBadChannelArgument($message, $channel)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteMessage($message, $channel);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testDeleteMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->deleteMessage('123', '123');
    }
}

