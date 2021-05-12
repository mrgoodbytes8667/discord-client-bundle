<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordTokenClient;


use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenResponse;
use Bytes\ResponseBundle\Enums\TokenSource;
use Bytes\ResponseBundle\HttpClient\Response\TokenResponse;
use Bytes\ResponseBundle\Test\AssertClientAnnotationsSameTrait;
use Bytes\Tests\Common\TestSerializerTrait;
use Generator;
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