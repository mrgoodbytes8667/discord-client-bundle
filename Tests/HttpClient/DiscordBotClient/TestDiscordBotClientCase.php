<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordBundle\Tests\JsonErrorCodesProviderTrait;
use Faker\Factory;
use Faker\Generator;

/**
 * Class TestDiscordBotClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class TestDiscordBotClientCase extends TestHttpClientCase
{
    use CommandProviderTrait, JsonErrorCodesProviderTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBotClient as setupClient;
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
}