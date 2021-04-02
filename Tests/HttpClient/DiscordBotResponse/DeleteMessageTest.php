<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteMessageTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteMessage;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteMessageInvalidReturnCode;
    }
}