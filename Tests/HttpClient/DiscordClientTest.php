<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use DateTime;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Internet;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
class DiscordClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait, \Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

    /**
     * @group legacy
     */
    public function testRequest()
    {
        $this->expectDeprecation('Since fakerphp/faker 1.14: Accessing property "word" is deprecated, use "word()" instead.');
        $this->expectDeprecation('Since fakerphp/faker 1.14: Accessing property "safeEmailDomain" is deprecated, use "safeEmailDomain()" instead.');
        /** @var Generator|Internet $faker */
        $faker = Factory::create();
        $content = $faker->randomHtml();

        $client = $this->setupClient(new MockHttpClient([
            new MockResponse($content),
        ]));

        $response = $client->request($faker->url());

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, $content);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordClient
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
    }

    /**
     * @dataProvider provideInvalidUrls
     * @param $url
     */
    public function testRequestInvalidUrl($url)
    {
        $client = $this->setupClient(new MockHttpClient());

        $this->expectException(InvalidArgumentException::class);
        $client->request($url);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidUrls()
    {
        yield ['url' => ''];
        yield ['url' => null];
        yield ['url' => []];
        yield ['url' => 1];
        yield ['url' => new DateTime()];
    }
}
