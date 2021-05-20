<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\TestEmptyResponseTrait;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LeaveGuildTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class LeaveGuildTest extends TestDiscordBotClientCase
{
    use TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testLeaveGuild;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testLeaveGuildInvalidReturnCode;
    }

    /**
     * Test that passing a null via leaveGuild() and no arg via deserialize throws an InvalidArgumentException
     */
    public function testDeserializationThrowsError()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "$type" must be provided');

        $this->assertTrue($this
            ->setupResponse(code: Response::HTTP_NO_CONTENT, type: null)
            ->deserialize());
    }
}