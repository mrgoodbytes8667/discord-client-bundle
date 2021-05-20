<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordUserClient;

use Bytes\DiscordClientBundle\Tests\HttpClient\TestDiscordClientTrait;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;

/**
 * Class DiscordUserClientTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordUserClient
 */
class DiscordUserClientTest extends TestDiscordUserClientCase
{
    use AssertClientAnnotationsSameTrait, TestDiscordClientTrait;

    /**
     *
     */
    public function testClientAnnotations()
    {
        $client = $this->setupRealUserClient();
        $this->assertClientAnnotationEquals('DISCORD', TokenSource::user(), $client);
    }

    /**
     *
     */
    public function testUsesClientAnnotations()
    {
        $this->assertUsesClientAnnotations($this->setupRealUserClient());
    }
}