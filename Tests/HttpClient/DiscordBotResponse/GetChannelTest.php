<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\Overwrite;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetChannelTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetChannelTest extends TestDiscordBotClientCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetChannel()
    {
        $channel = $this
            ->setupResponse('HttpClient/get-channel-v8-success.json', type: Channel::class)
            ->deserialize();

        $this->assertInstanceOf(Channel::class, $channel);

        $this->assertEquals('283321764683376549', $channel->getId());
        $this->assertEquals(3, $channel->getType());
        $this->assertEquals('745960888346529633', $channel->getGuildId());
        $this->assertEquals(1, $channel->getPosition());
        $this->assertEquals('Dolorem consequatur nulla non et reprehenderit quia. Repellat accusamus voluptatibus sed doloribus.', $channel->getName());

        $this->assertCount(3, $channel->getPermissionOverwrites());
        $overwrite = $channel->getPermissionOverwrites()[0];

        $this->assertInstanceOf(Overwrite::class, $overwrite);
        $this->assertEquals("348807741285566729", $overwrite->getId());
        $this->assertEquals('member', $overwrite->getType());
        $this->assertEquals('0', $overwrite->getAllow());
        $this->assertEquals('1678753900', $overwrite->getDeny());
    }
}
