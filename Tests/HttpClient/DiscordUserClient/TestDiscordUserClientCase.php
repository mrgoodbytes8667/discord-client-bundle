<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordUserClient;


use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordBundle\Tests\JsonErrorCodesProviderTrait;

/**
 * Class TestDiscordUserClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordUserClient
 */
class TestDiscordUserClientCase extends TestHttpClientCase
{
    use CommandProviderTrait, JsonErrorCodesProviderTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupUserClient as setupClient;
    }
}
