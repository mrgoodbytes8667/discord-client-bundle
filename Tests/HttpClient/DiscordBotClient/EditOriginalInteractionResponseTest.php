<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
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
 * Class EditOriginalInteractionResponseTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class EditOriginalInteractionResponseTest extends TestDiscordBotClientCase
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
    public function testEditOriginalInteractionResponse($token, $content, $embeds, $allowedMentions, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/edit-original-interaction-response-success.json'),
        ]));

        $response = $client->editOriginalInteractionResponse($token, $content, $embeds, $allowedMentions, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/edit-original-interaction-response-success.json'));
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
        $client->editOriginalInteractionResponse($this->faker->refreshToken(), $this->faker->paragraph());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testEditOriginalInteractionResponseValidationFailure()
    {
        $client = $this->setupClient(MockClient::empty());

        $description = $this->faker->paragraphsMinimumChars(2000);

        $this->expectException(ValidatorException::class);

        $client->editOriginalInteractionResponse($this->faker->refreshToken(), WebhookContent::create(content: $description));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditOriginalInteractionResponseExpiredInteractionToken()
    {
        $client = $this->setupClient(MockClient::jsonErrorCode(JsonErrorCodes::INVALID_WEBHOOK_TOKEN_PROVIDED, 'Invalid Webhook Token', Response::HTTP_UNAUTHORIZED));

        $response = $client->editOriginalInteractionResponse($this->faker->refreshToken(), $this->faker->paragraph());

        $this->assertResponseStatusCodeSame($response, Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData(JsonErrorCodes::INVALID_WEBHOOK_TOKEN_PROVIDED, 'Invalid Webhook Token'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_UNAUTHORIZED));

        $response->getContent();
    }
}