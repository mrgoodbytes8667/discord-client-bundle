<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\TestDiscordGuildTrait;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetGuildTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetGuildTest extends TestDiscordBotClientCase
{
    use TestDiscordGuildTrait;

    /**
     * @dataProvider provideValidGetGuildFixtureFiles
     *
     * @param string $file
     * @param bool $withCounts
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuild(string $file, bool $withCounts)
    {
        $guild = $this
            ->setupResponse($file, type: Guild::class)
            ->deserialize();

        $this->validateClientGetGuildAsGuild($guild, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', '282017982734073856', 2, $withCounts);
    }

    /**
     * @dataProvider provideValidGetGuildFixtureFiles
     *
     * @param string $file
     * @param bool $withCounts
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuildPartial(string $file, bool $withCounts)
    {
        $guild = $this
            ->setupResponse($file, type: PartialGuild::class)
            ->deserialize();

        $this->validateClientGetGuildAsPartialGuild($guild, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', $withCounts);
    }

    /**
     * @return Generator
     */
    public function provideValidGetGuildFixtureFiles()
    {
        yield ['file' => 'HttpClient/get-guild-success.json', 'withCounts' => false];
        yield ['file' => 'HttpClient/get-guild-with-counts-success.json', 'withCounts' => true];
    }
}

