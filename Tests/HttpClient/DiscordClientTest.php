<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient;

use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\HttpClient\MockClientResponse;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Internet;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DiscordClientTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient
 */
class DiscordClientTest extends TestHttpClientCase
{
    use AssertClientAnnotationsSameTrait, TestDiscordClientTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBaseClient as setupClient;
    }

    /**
     * @dataProvider provideContent
     * @param $content
     * @param $headers
     * @param $url
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testRequest($content, $headers, $url)
    {
        $client = $this->setupClient(new MockHttpClient([
            new MockResponse($content, [
                'response_headers' => $headers
            ]),
        ]));

        $response = $client->request($url, caller: __METHOD__);

        $this->validateRequestResponse($response, $content);

        $headers = $response->getHeaders();
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('x-lorem-ipsum', $headers);
        $this->assertCount(1, $headers['x-lorem-ipsum']);
        $this->assertEquals('Dolor', array_shift($headers['x-lorem-ipsum']));
    }

    /**
     * @param $response
     * @param $content
     */
    protected function validateRequestResponse($response, $content)
    {
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, $content);
    }

    /**
     * @return \Generator
     */
    public function provideContent()
    {
        /** @var Generator|Internet $faker */
        $faker = Factory::create();
        $content = $faker->randomHtml();
        $headers = [
            'X-Lorem-Ipsum' => 'Dolor'
        ];

        yield ['content' => $content, 'headers' => $headers, 'url' => $faker->url()];
    }

    /**
     * @dataProvider provideInvalidUrls
     * @param $url
     */
    public function testRequestInvalidUrl($url)
    {
        $client = $this->setupClient(new MockHttpClient());

        $this->expectException(InvalidArgumentException::class);
        $client->request($url, caller: __METHOD__);
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

    /**
     * @dataProvider provideContent
     * @group legacy
     * @param $content
     * @param $headers
     * @param $url
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testRequestWithResponseClassString($content, $headers, $url)
    {
        $client = $this->setupClient(new MockHttpClient([
            new MockResponse($content),
        ]));

        $response = $client->request($url, caller: __METHOD__, responseClass: MockClientResponse::class);

        $this->validateRequestResponse($response, $content);

        $this->assertInstanceOf(ClientResponseInterface::class, $response);
        $this->assertInstanceOf(MockClientResponse::class, $response);
    }

    /**
     * @dataProvider provideContent
     * @group legacy
     * @param $content
     * @param $headers
     * @param $url
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testRequestWithResponseClass($content, $headers, $url)
    {
        $client = $this->setupClient(new MockHttpClient([
            new MockResponse($content),
        ]));

        $mock = $this
            ->getMockBuilder(ClientResponseInterface::class)
            ->getMock();
        $mock->method('withResponse')
            ->willReturnSelf();

        $response = $client->request($url, caller: __METHOD__, responseClass: $mock);

        $this->assertInstanceOf(ClientResponseInterface::class, $response);
    }

    /**
     *
     */
    public function testClientAnnotations()
    {
        $actual = $this->setupClient();
        self::assertEquals('DISCORD', $actual->getIdentifier());
        self::assertNull($actual->getTokenSource());
    }

    /**
     *
     */
    public function testUsesClientAnnotations()
    {
        $actual = $this->setupClient();
        $reader = $this->getMockBuilder(Reader::class)->getMock();

        $reader->expects($this->exactly(2))
            ->method('getClassAnnotation');

        $actual->setReader($reader);

        $actual->getIdentifier();
        $actual->getTokenSource();

        $actual->setReader(new AnnotationReader());
        $this->assertNotEmpty($actual->getIdentifier());
        $this->assertNull($actual->getTokenSource());
    }
}