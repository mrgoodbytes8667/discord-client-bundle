<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteCommandTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteCommandTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteCommand;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteCommandInvalidReturnCode;
    }
}
