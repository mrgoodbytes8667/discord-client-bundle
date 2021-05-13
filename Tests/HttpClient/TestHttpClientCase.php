<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockStandaloneResponse;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Test\AssertClientResponseTrait;
use Bytes\Tests\Common\Constraint\ResponseContentSame;
use Bytes\Tests\Common\Constraint\ResponseStatusCodeSame;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use ErrorException;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Test\Constraint as ResponseConstraint;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class TestHttpClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
abstract class TestHttpClientCase extends TestCase
{
    use AssertClientResponseTrait, TestFullSerializerTrait, TestFullValidatorTrait;

    /**
     * @param bool $expected
     * @param $actual
     * @param string $message
     */
    public static function assertShouldBeNull($expected, $actual, string $message = ''): void {
        if($expected === true) {
            static::assertNull($actual, $message);
        } elseif ($expected === false) {
            static::assertNotNull($actual, $message);
        } else {
            throw new \InvalidArgumentException('Expected should be a boolean');
        }
    }

    /**
     * @param string|null $fixtureFile
     * @param null $content
     * @param int $code
     * @param string $type
     * @param array $context
     * @param callable|null $onSuccessCallable
     * @return ClientResponseInterface
     */
    public function setupResponse(?string $fixtureFile = null, $content = null, int $code = Response::HTTP_OK, $type = stdClass::class, array $context = [], ?callable $onSuccessCallable = null): ClientResponseInterface
    {
        $response = new MockStandaloneResponse(content: $content, fixtureFile: $fixtureFile, statusCode: $code);

        return \Bytes\ResponseBundle\HttpClient\Response\Response::make($this->serializer)->withResponse($response, $type, $context, onSuccessCallable: $onSuccessCallable);
    }

    /**
     * @return string
     */
    protected static function getRandomEmoji()
    {
        return self::getFaker()->emoji();
    }

    /**
     * @return Discord|Generator|MiscProvider
     */
    private static function getFaker()
    {
        /** @var Generator|Discord $faker */
        $faker = Factory::create();
        $faker->addProvider(new Discord($faker));

        return $faker;
    }

    /**
     * @return \Generator
     */
    public function provideBooleans()
    {
        yield [true];
        yield [false];
    }

    /**
     * @return \Generator
     */
    public function provideBooleansAndNull()
    {
        yield [true];
        yield [false];
        yield [null];
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return mixed
     */
    abstract protected function setupClient(HttpClientInterface $httpClient);
}