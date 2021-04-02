<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class LeaveGuildTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class LeaveGuildTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testLeaveGuild;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testLeaveGuildInvalidReturnCode;
    }
}
