<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteMessageTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteMessage;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteMessageInvalidReturnCode;
    }
}