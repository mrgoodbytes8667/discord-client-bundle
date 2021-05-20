<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordTokenClient;


use Bytes\DiscordClientBundle\HttpClient\Token\DiscordUserTokenResponse;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;
use Bytes\Tests\Common\TestSerializerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class DiscordTokenResponseTest
 * @package Bytes\TwitchClientBundle\Tests\HttpClient\TwitchResponse
 */
class DiscordTokenResponseTest extends TestCase
{
    use AssertClientAnnotationsSameTrait, TestSerializerTrait;

    /**
     *
     */
    public function testClientAnnotations()
    {
        $this->assertClientAnnotationEquals('DISCORD', TokenSource::user(), new DiscordUserTokenResponse($this->createSerializer(), new EventDispatcher()));
    }

    /**
     *
     */
    public function testUsesClientAnnotations()
    {
        $this->assertNotUsesClientAnnotations(new DiscordUserTokenResponse($this->createSerializer(), new EventDispatcher()));
    }
}