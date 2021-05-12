<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\HttpClient\TestDiscordClientTrait;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;

/**
 * Class DiscordBotClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class DiscordBotClientTest extends TestDiscordBotClientCase
{
    use AssertClientAnnotationsSameTrait, TestDiscordClientTrait;

    /**
     *
     */
    public function testClientAnnotations()
    {
        $client = $this->setupClient();
        $this->assertClientAnnotationEquals('DISCORD', TokenSource::app(), $client);
    }

    /**
     *
     */
    public function testUsesClientAnnotations()
    {
        $this->assertUsesClientAnnotations($this->setupClient());
    }
}
