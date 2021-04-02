<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\HttpClient\TestEmptyResponseTrait;

/**
 * Class CreateReactionTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class CreateReactionTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testCreateReaction;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testCreateReactionInvalidReturnCode;
    }
}
