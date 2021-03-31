<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordBundle\Tests\JsonErrorCodesProviderTrait;
use Faker\Factory;
use Faker\Generator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class TestDiscordBotClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class TestDiscordBotClientCase extends TestHttpClientCase
{
    use CommandProviderTrait, JsonErrorCodesProviderTrait;

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
     * @return DiscordBotClient
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordBotClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
    }
}