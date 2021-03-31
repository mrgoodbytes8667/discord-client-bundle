<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\TestUrlGeneratorTrait;

/**
 * Class DiscordTokenClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 *
 * @requires PHPUnit >= 9
 */
class DiscordTokenClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait, TestUrlGeneratorTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupTokenClient as setupClient;
    }
}
