<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Message\WebhookContent;
use Generator;

/**
 * Trait WebhookProviderTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
trait WebhookProviderTrait
{
    use TestDiscordFakerTrait;

    /**
     * @return Generator
     */
    public function provideWebhookArgs()
    {
        $this->setupFaker();

        yield ['id' => $this->faker->snowflake(), 'token' => $this->faker->accessToken(), 'content' => 'Hello, World!', 'embeds' => Embed::create('Hello, Embed!', description: 'This is an embedded message.'), 'allowedMentions' => null, 'username' => null, 'avatarUrl' => null, 'tts' => false];
        yield ['id' => $this->faker->snowflake(), 'token' => $this->faker->accessToken(), 'content' => WebhookContent::create(Embed::create('Hello, Embed!', description: 'This is an embedded message.'), content: 'Hello, World!', tts: false), 'embeds' => null, 'allowedMentions' => null, 'username' => null, 'avatarUrl' => null, 'tts' => false];
    }
}