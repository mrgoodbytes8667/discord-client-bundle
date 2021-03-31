<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;

/**
 * Trait ReactionsProviderTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
trait ReactionsProviderTrait
{
    use MessageProviderTrait;

    /**
     * @return \Generator
     */
    public function provideValidCreateReaction()
    {
        $this->setupFaker();

        foreach($this->provideValidDeleteMessages() as $message) {
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $this->faker->globalEmoji()];
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $this->faker->customEmoji()];
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $this->faker->emoji()];
        }
    }

}