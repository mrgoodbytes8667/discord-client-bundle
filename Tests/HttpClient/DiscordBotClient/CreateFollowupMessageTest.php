<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Message\WebhookContent;
use Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateFollowupMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class CreateFollowupMessageTest extends TestDiscordBotClientCase
{
    use TestDiscordFakerTrait;

    /**
     * @dataProvider provideFollowups
     * @param $token
     * @param $content
     * @param $embeds
     * @param $allowedMentions
     * @param $tts
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCreateFollowupMessage($token, $content, $embeds, $allowedMentions, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/create-followup-message-success.json'),
        ]));

        $response = $client->createFollowupMessage($token, $content, $embeds, $allowedMentions, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/create-followup-message-success.json'));
    }

    /**
     * @return Generator
     */
    public function provideFollowups()
    {
        $this->setupFaker();

        yield ['token' => $this->faker->accessToken(), 'content' => 'Hello, World!', 'embeds' => Embed::create('Hello, Embed!', description: 'This is an embedded message.'), 'allowedMentions' => null, 'tts' => false];
        yield ['token' => $this->faker->accessToken(), 'content' => WebhookContent::create(Embed::create('Hello, Embed!', description: 'This is an embedded message.'), content: 'Hello, World!', tts: false), 'embeds' => null, 'allowedMentions' => null, 'tts' => false];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testCreateFollowupMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));
        $client->createFollowupMessage($this->faker->refreshToken(), $this->faker->paragraph());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateFollowupMessageValidationFailure()
    {
        $client = $this->setupClient(MockClient::empty());

        $description = $this->faker->paragraphsMinimumChars(2000);

        $this->expectException(ValidatorException::class);

        $client->createFollowupMessage($this->faker->refreshToken(), WebhookContent::create(content: $description));
    }

    /**
     * @todo change to JsonErrorCodes::INVALID_WEBHOOK_TOKEN
     */
    public function testCreateFollowupMessageExpiredInteractionToken()
    {
        $client = $this->setupClient(MockClient::jsonErrorCode(50027, 'Invalid Webhook Token', Response::HTTP_UNAUTHORIZED));

        $response = $client->createFollowupMessage($this->faker->refreshToken(), $this->faker->paragraph());

        $this->assertResponseStatusCodeSame($response, Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData(50027, 'Invalid Webhook Token'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_UNAUTHORIZED));

        $response->getContent();
    }
}