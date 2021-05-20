<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteFollowupMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteFollowupMessageTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteFollowupMessage;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteFollowupMessageInvalidReturnCode;
    }
}