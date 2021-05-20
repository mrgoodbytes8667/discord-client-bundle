<?php


namespace Bytes\DiscordClientBundle\Tests\Command;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockUnauthorizedCallback
 * @package Bytes\DiscordClientBundle\Tests\Command
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