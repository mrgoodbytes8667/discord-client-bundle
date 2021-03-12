<?php


namespace Bytes\DiscordBundle\Tests\Command;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockUnauthorizedCallback
 * @package Bytes\DiscordBundle\Tests\Command
 */
class MockUnauthorizedCallback extends MockClientCallbackIterator
{
    /**
     * MockUnauthorizedCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->add(MockJsonResponse::makeFixture('unauthorized-v6.json', Response::HTTP_UNAUTHORIZED));
    }
}