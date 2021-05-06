<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordTokenClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Routing\DiscordUserOAuth;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\TestUrlGeneratorTrait;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\User;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Bytes\Tests\Common\ClientExceptionResponseProviderTrait;
use Bytes\Tests\Common\Constraint\DateIntervalSame;
use Bytes\Tests\Common\TestDateIntervalTrait;
use DateInterval;
use Exception;
use Generator;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DiscordUserTokenClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 *
 * @requires PHPUnit >= 9
 */
class DiscordUserTokenClientTest extends TestHttpClientCase
{
    use TestDateIntervalTrait, ClientExceptionResponseProviderTrait, TestDiscordFakerTrait, TestUrlGeneratorTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupUserTokenClient as setupClient;
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
    public function testTokenExchange()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/token-exchange-with-guild-success.json')
        ]));

        $code = ByteString::fromRandom(30);
        $redirect = 'https://www.example.com';


        $response = $client->exchange($code, url: $redirect);
        $this->assertInstanceOf(Token::class, $response);

        $this->assertEquals('Bearer', $response->getTokenType());
        $this->assertEquals('v6XtvrnWt1D3R6YFzSejQoBv6oVW5W', $response->getAccessToken());
        $this->assertDateIntervalEquals('P7D', $response->getExpiresIn());
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
        $redirect = 'https://www.example.com';
        $oAuth = new DiscordUserOAuth(ByteString::fromRandom(30), [
            'bot' => [
                'redirects' => [
                    'method' => 'url',
                    'url' => $redirect,
                ]
            ],
            'login' => [
                'redirects' => [
                    'method' => 'url',
                    'url' => $redirect,
                ]
            ],
            'user' => [
                'redirects' => [
                    'method' => 'url',
                    'url' => $redirect,
                ]
            ],
        ]);
        $oAuth->setValidator($this->validator);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/token-exchange-with-guild-success.json')
        ]))
            ->setOAuth($oAuth);

        $code = ByteString::fromRandom(30);

        $response = $client->refreshToken(Token::createFromAccessToken('')
            ->setRefreshToken(''));
        $this->assertNull($response);

        $response = $client->refreshToken(Token::createFromAccessToken(ByteString::fromRandom(30)->toString())
            ->setRefreshToken(ByteString::fromRandom(30)->toString()));
        $this->assertInstanceOf(Token::class, $response);

        $this->assertEquals('Bearer', $response->getTokenType());
        $this->assertEquals('v6XtvrnWt1D3R6YFzSejQoBv6oVW5W', $response->getAccessToken());
        $this->assertDateIntervalEquals('P7D', $response->getExpiresIn());
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

        $client->exchange($code, $redirect);
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
            $client->exchange($code, $redirect);
        } catch (ClientExceptionInterface $exception) {
            $json = $exception->getResponse()->getContent(false);
            $response = $this->serializer->deserialize($json, Token::class, 'json');
            $this->assertEquals($error, $response->getError());
            $this->assertEquals($description, $response->getErrorDescription());

            $this->assertNull($response->getTokenType());
            $this->assertNull($response->getAccessToken());
            $this->assertNull($response->getExpiresIn());
            $this->assertNull($response->getRefreshToken());
            $this->assertEmpty($response->getScope());
            $this->assertNull($response->getGuild());
        }
    }

    /**
     * @throws Exception
     */
    public function testValidateToken()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-current-authorization-information-success.json')
        ]));

        $accessToken = Token::createFromAccessToken($this->faker->accessToken());

        $token = $client->validateToken($accessToken);
        $this->assertInstanceOf(TokenValidationResponseInterface::class, $token);
        $this->assertInstanceOf(User::class, $token);

        $this->assertTrue($token->isMatch(clientId: Fixture::CLIENT_ID, userName: 'caltenwerth', userId: '108363497252953230'));
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testValidateTokenFailure(int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));

        $this->assertNull($client->validateToken(Token::createFromAccessToken($this->faker->accessToken())));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
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
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testValidateTokenInvalidContent()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::make(data: ['expires' => 'abc123'])
        ]));

        $this->assertNull($client->validateToken(Token::createFromAccessToken($this->faker->accessToken())));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testRevokeToken()
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->revokeToken(Token::createFromAccessToken($this->faker->accessToken()));

        $this->assertInstanceOf(ClientResponseInterface::class, $response);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }
}
