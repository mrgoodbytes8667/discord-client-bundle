<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordTokenClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordClientBundle\Tests\TestUrlGeneratorTrait;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\Bot;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Bytes\Tests\Common\ClientExceptionResponseProviderTrait;
use Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

/**
 * Class DiscordBotTokenClientTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient
 *
 * @requires PHPUnit >= 9
 */
class DiscordBotTokenClientTest extends TestHttpClientCase
{
    use AssertClientAnnotationsSameTrait, ClientExceptionResponseProviderTrait, TestDiscordFakerTrait, TestUrlGeneratorTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBotTokenClient as setupClient;
    }

    /**
     *
     */
    public function testTokenExchangeGetToken()
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->exchange($this->faker->randomAlphanumericString(), url: $this->faker->url());
        $this->assertEquals(Token::createFromAccessToken(Fixture::BOT_TOKEN), $response);

        $response = $client->getToken();
        $this->assertEquals(Token::createFromAccessToken(Fixture::BOT_TOKEN), $response);

        $response = $client->refreshToken(Token::createFromAccessToken(ByteString::fromRandom(30)->toString())
            ->setRefreshToken(ByteString::fromRandom(30)->toString()));
        $this->assertEquals(Token::createFromAccessToken(Fixture::BOT_TOKEN), $response);
    }

    /**
     * @throws \Exception
     */
    public function testRevokeToken()
    {
        $client = $this->setupClient(MockClient::empty());

        $this->expectException(\LogicException::class);

        $client->revokeToken(Token::createFromAccessToken($this->faker->accessToken()));

    }

    /**
     * @throws \Exception
     */
    public function testValidateToken()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-current-bot-application-information-success.json')
        ]));

        $accessToken = Token::createFromAccessToken($this->faker->accessToken());

        $token = $client->validateToken($accessToken);
        $this->assertInstanceOf(TokenValidationResponseInterface::class, $token);
        $this->assertInstanceOf(Bot::class, $token);

        $this->assertTrue($token->isMatch(clientId: Fixture::CLIENT_ID, userName: 'moore.belle'));
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testValidateTokenFailure(int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));

        $this->assertNull($client->validateToken(Token::createFromAccessToken($this->faker->accessToken())));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testValidateTokenUnauthorized()
    {
        $client = $this->setupClient(MockClient::jsonErrorCode(0, '401: Unauthorized', Response::HTTP_UNAUTHORIZED));

        $token = $client->validateToken(Token::createFromAccessToken($this->faker->accessToken()));
        $this->assertEquals('401: Unauthorized', $token->getMessage());
        $this->assertEquals(0, $token->getCode());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testValidateTokenInvalidContent()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::make(data: ['description' => new \DateTimeImmutable()])
        ]));

        $this->assertNull($client->validateToken(Token::createFromAccessToken($this->faker->accessToken())));
    }

    /**
     *
     */
    public function testClientAnnotations()
    {
        $client = $this->setupClient();
        $this->assertClientAnnotationEquals('DISCORD', TokenSource::app(), $client);
    }

    /**
     *
     */
    public function testUsesClientAnnotations()
    {
        $this->assertUsesClientAnnotations($this->setupClient());
    }
}

