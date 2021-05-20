<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class DeleteOriginalInteractionResponseTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteOriginalInteractionResponseTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteOriginalInteractionResponse;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteOriginalInteractionResponseInvalidReturnCode;
    }
}