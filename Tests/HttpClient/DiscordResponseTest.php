<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\ResponseBundle\HttpClient\Response\Response;
use Bytes\Tests\Common\ClientExceptionResponseProviderTrait;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Faker\Factory;
use Generator;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response as Http;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function Symfony\Component\String\u;

/**
 * Class DiscordResponseTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
class DiscordResponseTest extends TestHttpClientCase
{
    use ClientExceptionResponseProviderTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBaseClient as setupClient;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestTimeout()
    {
        $body = function () {
            // empty strings are turned into timeouts so that they are easy to test
            yield '';
            yield '';
            yield '';
            yield '';
            yield '';
            yield '';
            yield '';
            yield '';
        };

        $client = $this->setupClient(new MockHttpClient([
            new MockResponse($body()),
        ]), [
            // Matches non-oauth API routes
            DiscordClient::SCOPE_API => [
                'headers' => ['User-Agent' => Fixture::USER_AGENT],
                'timeout' => 2.5,
            ]
        ]);

        $this->expectException(TransportExceptionInterface::class);

        $client->getMe()->isSuccess();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetStatusCodeWithException()
    {
        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $response->method('getStatusCode')
            ->willThrowException(new TransportException());

        $discordResponse = Response::make($this->serializer)->withResponse($response, null);

        $this->expectException(TransportException::class);
        $discordResponse->getStatusCode();
    }

    /**
     * @dataProvider provide200Responses
     * @param $code
     * @param $success
     */
    public function testIsSuccess($code, $success)
    {
        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $response->method('getStatusCode')
            ->willReturn($code);

        $discordResponse = Response::make($this->serializer)->withResponse($response, null);

        $this->assertTrue($discordResponse->isSuccess());
    }

    /**
     * @dataProvider provide100Responses
     * @dataProvider provide300Responses
     * @dataProvider provide400Responses
     * @dataProvider provide500Responses
     * @param $code
     * @param $success
     */
    public function testIsNotSuccess($code, $success)
    {
        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $response->method('getStatusCode')
            ->willReturn($code);

        $discordResponse = Response::make($this->serializer)->withResponse($response, null);

        $this->assertFalse($discordResponse->isSuccess());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testIsSuccessWithException()
    {
        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $response->method('getStatusCode')
            ->willThrowException(new TransportException());

        $discordResponse = Response::make($this->serializer)->withResponse($response, null);

        $this->assertFalse($discordResponse->isSuccess());
    }

    /**
     * @dataProvider provideEmptySuccessfulResponse
     * @param $response
     * @param $headers
     */
    public function testPassthroughMethods($response, $headers)
    {
        // To cover getStatusCode() in Response
        $this->assertEquals(Http::HTTP_OK, $response->getStatusCode());

        $this->assertCount(1, $response->getHeaders());
    }

    /**
     * @dataProvider provideEmptySuccessfulResponse
     * @param $response
     * @param $headers
     */
    public function testGetType($response, $headers)
    {
        // To cover getType() in Response
        $this->assertNull($response->getType());
    }

    /**
     * @dataProvider provideEmptySuccessfulResponse
     * @param $response
     * @param $headers
     */
    public function testGetDeserializeContext($response, $headers)
    {
        // To cover getDeserializeContext() in Response
        $this->assertEmpty($response->getDeserializeContext());
    }

    /**
     * @dataProvider provideEmptySuccessfulResponse
     * @param $response
     * @param $headers
     */
    public function testGetOnSuccessCallable($response, $headers)
    {
        // To cover getOnSuccessCallable() in Response
        $this->assertNull($response->getOnSuccessCallable());
    }

    /**
     * @return Generator
     */
    public function provideEmptySuccessfulResponse()
    {
        /** @var \Faker\Generator|Discord|MiscProvider $faker */
        $faker = Factory::create();
        $faker->addProvider(new Discord($faker));

        $this->setUpSerializer();

        $header = u($faker->randomAlphanumericString(10, 'abcdefghijkmnopqrstuvwxyz'))->lower()->prepend('x-')->toString();

        $value = $faker->word();
        $headers[$header] = $value;

        $ri = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $ri->method('getStatusCode')
            ->willReturn(Http::HTTP_OK);
        $ri->method('getHeaders')
            ->willReturn([
                $header => [
                    $value
                ]
            ]);

        yield ['response' => Response::make($this->serializer)->withResponse($ri, null), 'headers' => $headers];
    }

    /**
     * @dataProvider provideEmptySuccessfulResponse
     * @param $response
     * @param $providedHeaders
     */
    public function testGetHeaders($response, $providedHeaders)
    {
        $count = count($providedHeaders);
        $providedHeaderKey = array_key_first($providedHeaders);
        $providedHeaderValue = array_shift($providedHeaders);

        $headers = $response->getHeaders();
        $this->assertCount($count, $headers);
        $this->assertArrayHasKey($providedHeaderKey, $headers);
        $header = array_shift($headers);
        $header = array_shift($header);
        $this->assertEquals($providedHeaderValue, $header);
    }
}