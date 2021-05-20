<?php


namespace Bytes\DiscordClientBundle\Tests;


use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Generator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait JsonErrorCodesProviderTrait
 * @package Bytes\DiscordClientBundle\Tests
 */
trait JsonErrorCodesProviderTrait
{
    /**
     * @return Generator
     */
    public function provideJsonErrorCodes()
    {
        foreach([$this->provideMissingAccess(), $this->provideUnknownGuild(), $this->provideGeneralErrorUnauthorized(), $this->provideUnknownEmoji()] as $generator) {
            foreach ($generator as $item) {
                yield ['jsonCode' => $item['jsonCode'], 'message' => $item['message'], 'httpCode' => $item['httpCode']];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideMissingAccess()
    {
        yield ['jsonCode' => JsonErrorCodes::MISSING_ACCESS(), 'message' => 'Missing Access', 'httpCode' => Response::HTTP_FORBIDDEN];
    }

    /**
     * @return Generator
     */
    public function provideUnknownGuild()
    {
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_GUILD(), 'message' => 'Unknown Guild', 'httpCode' => Response::HTTP_NOT_FOUND];
    }

    /**
     * @return Generator
     */
    public function provideGeneralErrorUnauthorized()
    {
        yield ['jsonCode' => JsonErrorCodes::GENERAL_ERROR(), 'message' => '401: Unauthorized', 'httpCode' => Response::HTTP_UNAUTHORIZED];
    }

    /**
     * @return Generator
     */
    public function provideUnknownEmoji()
    {
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_EMOJI(), 'message' => 'Unknown Emoji', 'httpCode' => Response::HTTP_BAD_REQUEST];
    }
}