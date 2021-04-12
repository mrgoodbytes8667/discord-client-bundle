<?php


namespace Bytes\DiscordBundle\Tests\MockHttpClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\Tests\Common\MockHttpClient\MockResponseHeaderInterface;
use Faker\Factory;
use Faker\Generator as FakerGenerator;

/**
 * Class MockDiscordResponseHeader
 * @package Bytes\DiscordBundle\Tests\MockHttpClient
 */
class MockDiscordResponseHeader implements MockResponseHeaderInterface
{
    /**
     * @inheritDoc
     */
    public function getRateLimitArray(): array
    {
        /** @var FakerGenerator|Discord $faker */
        $faker = Factory::create();
        $faker->addProvider(new MiscProvider($faker));
        $faker->addProvider(new Discord($faker));

        return $faker->rateLimitArray();
    }
}