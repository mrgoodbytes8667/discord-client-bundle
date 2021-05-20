<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordUserClient;


use Bytes\DiscordClientBundle\Tests\CommandProviderTrait;
use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordClientBundle\Tests\JsonErrorCodesProviderTrait;

/**
 * Class TestDiscordUserClientCase
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordUserClient
 */
class TestDiscordUserClientCase extends TestHttpClientCase
{
    use CommandProviderTrait, JsonErrorCodesProviderTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupUserClient as setupClient;
    }
}
