<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Exceptions\UnknownObjectException;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
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

    /**
     * @dataProvider provideClientExceptionResponses
     * @param int $code
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws NoTokenException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testDeleteMessageFailureUnknownMessage(int $code)
    {
        $this->expectException(UnknownObjectException::class);

        $client = $this->setupClient(MockClient::jsonErrorCode(JsonErrorCodes::UNKNOWN_MESSAGE, '', $code));

        $client->deleteMessage('123', '123');
    }
}

