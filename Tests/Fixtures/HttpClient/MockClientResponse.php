<?php


namespace Bytes\DiscordBundle\Tests\Fixtures\HttpClient;


use Bytes\DiscordBundle\HttpClient\DiscordResponse;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;


/**
 * Class MockClientResponse
 * @package Bytes\DiscordBundle\Tests\Fixtures\HttpClient
 */
class MockClientResponse extends DiscordResponse implements ClientResponseInterface
{
}