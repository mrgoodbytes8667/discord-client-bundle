<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteCommandTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteCommand;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteCommandInvalidReturnCode;
    }
}
