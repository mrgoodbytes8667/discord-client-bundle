<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordClient;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordUserClient;
use Bytes\DiscordClientBundle\HttpClient\Token\DiscordBotTokenClient;
use Bytes\DiscordClientBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\Tests\Common\ClientExceptionResponseProviderTrait;
use Bytes\DiscordClientBundle\Tests\CommandProviderTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\ResponseBundle\Interfaces\IdInterface;
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
 * @package Bytes\DiscordClientBundle\Tests\HttpClient
 *
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method expectException(string $exception)
 * @method expectExceptionMessage(string $message)
 * @method DiscordClient|DiscordBotClient|DiscordUserClient|DiscordBotTokenClient|DiscordUserTokenClient setupClient(HttpClientInterface $httpClient = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
 * @property SerializerInterface $serializer
 */
trait TestDiscordClientTrait
{
    use CommandProviderTrait, ClientExceptionResponseProviderTrait, WebhookProviderTrait;

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