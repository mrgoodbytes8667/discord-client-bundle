<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Message\Content;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class CreateMessageTest extends TestDiscordBotClientCase
{
    use MessageProviderTrait;

    /**
     * @dataProvider provideCreateEditMessage
     */
    public function testCreateMessage($channel, $message, $content, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-message-success.json'),
        ]));

        $response = $client->createMessage($channel, $content, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-message-success.json'));
    }

    /**
     * @dataProvider provideCreateEditMessage
     * @param $channel
     * @param $message
     * @param $content
     * @param $tts
     * @throws TransportExceptionInterface
     * @throws NoTokenException
     */
    public function testCreateMessageEmptyStringContent($channel, $message, $content, $tts)
    {
        $this->expectException(ValidatorException::class);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-message-success.json'),
        ]));

        $client->createMessage($channel, '', $tts);
    }

    /**
     * @dataProvider provideCreateEditMessage
     * @param $channel
     * @param $message
     * @param $content
     * @param $tts
     * @throws TransportExceptionInterface
     * @throws NoTokenException
     */
    public function testCreateMessageValidationFailure($channel, $message, $content, $tts)
    {
        $this->expectException(ValidatorException::class);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-message-success.json'),
        ]));

        $content = new Content();
        foreach (range(1, 5) as $index) {
            $content->addStickerId($this->faker->snowflake());
        }

        $client->createMessage($channel, $content, $tts);
    }

    /**
     * @dataProvider provideInvalidChannelValidContent
     * @param $channel
     * @param $content
     * @param $tts
     * @throws NoTokenException
     * @throws TransportExceptionInterface
     */
    public function testCreateMessageBadChannelArgument($channel, $content, $tts)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createMessage($channel, $content, $tts);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     * @param int $code
     * @throws NoTokenException
     * @throws TransportExceptionInterface
     */
    public function testCreateMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->createMessage('123', '123');
    }
}
