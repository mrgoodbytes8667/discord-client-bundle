<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\Services\Client\DiscordUser;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordUserTest
 * @package Bytes\DiscordBundle\Tests\Services
 */
class DiscordUserTest extends TestCase
{
    use TestFullValidatorTrait, TestFullSerializerTrait, TestDiscordTrait, CommandProviderTrait, DiscordClientSetupTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordUser
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        $client = $this->setupUserClient($httpClient);
        return new DiscordUser($client, $this->serializer);
    }
}
