<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use DateTime;
use Generator;

/**
 * Trait GuildProviderTrait
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
trait GuildProviderTrait
{
    /**
     * @return Generator
     */
    public function provideInvalidGetGuildArguments()
    {
        yield ['guild' => ''];
        yield ['guild' => null];
        yield ['guild' => new DateTime()];
        yield ['guild' => []];
    }

    /**
     * @return Generator
     */
    public function provideInvalidNotEmptyGetGuildArguments()
    {
        yield ['guild' => new DateTime()];
    }

    /**
     * @return Generator
     */
    public function provideValidGuilds()
    {
        $mock = $this
            ->getMockBuilder(GuildIdInterface::class)
            ->getMock();
        $mock->method('getGuildId')
            ->willReturn('230858112993375816');
        yield [$mock];

        $mock = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $mock->method('getId')
            ->willReturn('230858112993375816');
        yield [$mock];

        yield ['230858112993375816'];
    }

    /**
     * @return Generator
     */
    public function provideEmptyGuilds()
    {
        yield [''];
        yield [null];
    }
}
