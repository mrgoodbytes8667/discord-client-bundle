<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\DiscordClientBundle\Tests\CommandProviderTrait;
use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestHttpClientCase;
use Bytes\DiscordClientBundle\Tests\JsonErrorCodesProviderTrait;

/**
 * Class TestDiscordBotClientCase
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class TestDiscordBotClientCase extends TestHttpClientCase
{
    use CommandProviderTrait, JsonErrorCodesProviderTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBotClient as setupClient;
    }
}
