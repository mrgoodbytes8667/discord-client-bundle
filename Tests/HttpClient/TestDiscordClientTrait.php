<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\Tests\ClientExceptionResponseProviderTrait;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockEmptyResponse;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\Message\WebhookContent;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use Faker\Factory;
use Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Trait TestDiscordClientTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient
 *
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method expectException(string $exception)
 * @method expectExceptionMessage(string $message)
 * @method setupClient(HttpClientInterface $httpClient)
 * @property SerializerInterface $serializer
 */
trait TestDiscordClientTrait
{
    use CommandProviderTrait, ClientExceptionResponseProviderTrait, WebhookProviderTrait;

    /**
     *
     */
    public function testTokenExchange()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/token-exchange-with-guild-success.json')
        ]));

        $code = ByteString::fromRandom(30);
        $redirect = 'https://www.example.com';


        $response = $client->tokenExchange($code, $redirect);
        $this->assertInstanceOf(Token::class, $response);

        $this->assertEquals('Bearer', $response->getTokenType());
        $this->assertEquals('v6XtvrnWt1D3R6YFzSejQoBv6oVW5W', $response->getAccessToken());
        $this->assertEquals(604800, $response->getExpiresIn());
        $this->assertEquals('tDpAVPmhq4PZqeXiXCTV6mRGvhgDu9', $response->getRefreshToken());
        $this->assertEquals('identify connections guilds bot applications.commands', $response->getScope());

        $this->assertInstanceOf(PartialGuild::class, $response->getGuild());
        $this->assertInstanceOf(Guild::class, $response->getGuild());

        // Test some fields that will only be present for full guild objects
        $this->assertCount(2, $response->getGuild()->getRoles());
        $this->assertEquals('711392223308032631', $response->getGuild()->getSystemChannelId());

        $this->assertNull($response->getMessage());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getRetryAfter());
        $this->assertNull($response->getGlobal());
    }

    /**
     *
     */
    public function testRefreshTokenExchange()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/token-exchange-with-guild-success.json')
        ]));

        $code = ByteString::fromRandom(30);
        $redirect = 'https://www.example.com';


        $response = $client->tokenExchange($code, $redirect, [], OAuthGrantTypes::refreshToken());
        $this->assertInstanceOf(Token::class, $response);

        $this->assertEquals('Bearer', $response->getTokenType());
        $this->assertEquals('v6XtvrnWt1D3R6YFzSejQoBv6oVW5W', $response->getAccessToken());
        $this->assertEquals(604800, $response->getExpiresIn());
        $this->assertEquals('tDpAVPmhq4PZqeXiXCTV6mRGvhgDu9', $response->getRefreshToken());
        $this->assertEquals('identify connections guilds bot applications.commands', $response->getScope());

        $this->assertInstanceOf(PartialGuild::class, $response->getGuild());
        $this->assertInstanceOf(Guild::class, $response->getGuild());

        // Test some fields that will only be present for full guild objects
        $this->assertCount(2, $response->getGuild()->getRoles());
        $this->assertEquals('711392223308032631', $response->getGuild()->getSystemChannelId());

        $this->assertNull($response->getMessage());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getRetryAfter());
        $this->assertNull($response->getGlobal());
    }

    /**
     * @dataProvider provideTokenExchangeResponses
     *
     * @param string $error
     * @param string $description
     * @param string $filename
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testTokenExchangeBadRequestsGeneric(string $error, string $description, string $filename)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($filename, Response::HTTP_BAD_REQUEST)
        ]));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_BAD_REQUEST));

        $code = ByteString::fromRandom(30);
        $redirect = 'https://www.example.com';

        $client->tokenExchange($code, $redirect);
    }

    /**
     * @dataProvider provideTokenExchangeResponses
     *
     * @param string $error
     * @param string $description
     * @param string $filename
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testTokenExchangeBadRequestsExplicit(string $error, string $description, string $filename)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($filename, Response::HTTP_BAD_REQUEST)
        ]));

        $code = ByteString::fromRandom(30);
        $redirect = 'https://www.example.com';

        try {
            $client->tokenExchange($code, $redirect);
        } catch (ClientExceptionInterface $exception) {
            $json = $exception->getResponse()->getContent(false);
            $response = $this->serializer->deserialize($json, Token::class, 'json');
            $this->assertEquals($error, $response->getError());
            $this->assertEquals($description, $response->getErrorDescription());

            $this->assertNull($response->getTokenType());
            $this->assertNull($response->getAccessToken());
            $this->assertNull($response->getExpiresIn());
            $this->assertNull($response->getRefreshToken());
            $this->assertNull($response->getScope());
            $this->assertNull($response->getGuild());
        }
    }

    /**
     * @return Generator
     */
    public function provideTokenExchangeResponses()
    {
        yield ['error' => 'invalid_request', 'description' => 'Invalid "code" in request.', 'filename' => 'HttpClient/token-exchange-failure-400-invalid-code.json'];
        yield ['error' => 'invalid_request', 'description' => 'Invalid "redirect_uri" in request.', 'filename' => 'HttpClient/token-exchange-failure-400-invalid-redirect.json'];
    }

    /**
     *
     */
    public function testGetGuilds()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guilds.json'),
        ]));

        $response = $client->getGuilds();

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-guilds.json'));
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetGuildsFailure(int $code)
    {
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $response = $client->getGuilds();

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideValidUsers
     * @param string $file
     * @param $userId
     * @throws TransportExceptionInterface
     */
    public function testGetUser(string $file, $userId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getUser($userId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    /**
     * @return Generator
     */
    public function provideValidUsers()
    {
        $user = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $user->method('getId')
            ->willReturn('230858112993375816');

        yield ['file' => 'HttpClient/get-user.json', 'userId' => '230858112993375816'];
        yield ['file' => 'HttpClient/get-user.json', 'userId' => $user];

        yield ['file' => 'HttpClient/get-me.json', 'userId' => '@me'];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetUserFailure(int $code)
    {
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $response = $client->getUser('230858112993375816');

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetMe()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-me.json'),
        ]));

        $response = $client->getMe();

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-me.json'));
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetMeFailure(int $code)
    {
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $response = $client->getMe();

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideWebhookArgs
     * @param $id
     * @param $token
     * @param $content
     * @param $embeds
     * @param $allowedMentions
     * @param $username
     * @param $avatarUrl
     * @param $tts
     * @throws TransportExceptionInterface
     */
    public function testExecuteWebhook($id, $token, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/execute-webhook-success.json'),
        ]));

        $response = $client->executeWebhook($id, $token, true, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/execute-webhook-success.json'));
    }

    /**
     * @dataProvider provideWebhookArgs
     * @param $id
     * @param $token
     * @param $content
     * @param $embeds
     * @param $allowedMentions
     * @param $username
     * @param $avatarUrl
     * @param $tts
     * @throws TransportExceptionInterface
     */
    public function testExecuteWebhookNoWait($id, $token, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts)
    {
        $client = $this->setupClient(MockClient::empty());

        $faker = $this->getFaker();

        $response = $client->executeWebhook($id, $token, false, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testExecuteWebhookValidationFailure()
    {
        $client = $this->setupClient(MockClient::empty());

        $faker = $this->getFaker();

        $description = $faker->paragraphsMinimumChars(2000);

        $this->expectException(ValidatorException::class);

        $client->executeWebhook($faker->snowflake(), $faker->snowflake(), $faker->snowflake(), WebhookContent::create(content: $description));
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testEditWebhookMessage()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/edit-webhook-message-success.json'),
        ]));

        $faker = $this->getFaker();

        $response = $client->editWebhookMessage($faker->snowflake(), $faker->snowflake(), $faker->snowflake(), 'Hello, oh edited World!', Embed::create('Hello, Embed!', description: 'This is an embedded message.'), tts: false);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/edit-webhook-message-success.json'));
    }

    /**
     * @dataProvider provideDeleteWebhookMessage
     * @param $id
     * @param $token
     * @param $messageId
     * @throws TransportExceptionInterface
     */
    public function testDeleteWebhookMessage($id, $token, $messageId)
    {
        $client = $this->setupClient(MockClient::empty());

        $faker = $this->getFaker();

        $response = $client->deleteWebhookMessage($id, $token, $messageId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @dataProvider provideDeleteWebhookMessageInvalidArgument
     * @param $id
     * @param $token
     * @param $messageId
     * @throws TransportExceptionInterface
     */
    public function testDeleteWebhookMessageBadChannelArgument($id, $token, $messageId)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteWebhookMessage($id, $token, $messageId);
    }

    /**
     * @return Generator
     */
    public function provideDeleteWebhookMessageInvalidArgument()
    {
        $faker = $this->getFaker();

        $message = new Message();
        $message->setChannelId('123');
        yield ['id' => $faker->snowflake(), 'token' => $faker->refreshToken(), 'messageId' => $message];
        yield ['id' => new \DateTime(), 'token' => $faker->refreshToken(), 'messageId' => $faker->snowflake()];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testDeleteWebhookMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        foreach($this->provideDeleteWebhookMessage() as $item) {
            $client->deleteWebhookMessage($item['id'], $item['token'], $item['messageId']);
        }
    }

    /**
     * @return Generator
     */
    public function provideDeleteWebhookMessage()
    {
        $faker = $this->getFaker();
        yield ['id' => $faker->snowflake(), 'token' => $faker->refreshToken(), 'messageId' => $faker->snowflake()];
    }

    /**
     * @return Discord|MiscProvider|\Faker\Generator
     */
    protected function getFaker()
    {
        /** @var \Faker\Generator|MiscProvider|Discord $faker */
        $faker = Factory::create();
        $faker->addProvider(new Discord($faker));

        return $faker;
    }
}