<?php


namespace Bytes\DiscordBundle\Tests\Fixtures\HttpClient;


use Bytes\ResponseBundle\HttpClient\Response\Response;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;


/**
 * Class MockClientResponse
 * @package Bytes\DiscordBundle\Tests\Fixtures\HttpClient
 */
class MockClientResponse extends Response implements ClientResponseInterface
{
}