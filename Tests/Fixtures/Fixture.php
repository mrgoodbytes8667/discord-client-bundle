<?php


namespace Bytes\DiscordClientBundle\Tests\Fixtures;


use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;

/**
 * Class Fixture
 * @package Bytes\DiscordClientBundle\Tests\Fixtures
 */
class Fixture
{
    /**
     * A randomly generated client ID
     */
    const CLIENT_ID = '586568566858692924';

    /**
     * A randomly generated client secret
     */
    const CLIENT_SECRET = 'RsgAvyEhVKD9Qrjeg2J9iFw1u5fQswmw';

    /**
     * A randomly generated bot token
     */
    const BOT_TOKEN = '84B.cGe2KLFHp6MWtMetEm5m7-mmDcYKuNr3XeuoKvkwBfPhunm.o.BTtNk';

    /**
     * A generic user agent
     */
    const USER_AGENT = 'Discord Bundle PHPUnit Test (https://www.github.com, 0.0.1)';

    /**
     * @param string $file
     * @return string
     */
    public static function getFixturesFile(string $file)
    {
        return __DIR__ . '/' . $file;
    }

    /**
     * @param string|null $file
     * @return string|null
     */
    public static function getFixturesData(?string $file): ?string
    {
        if(empty($file))
        {
            return null;
        }
        return file_get_contents(self::getFixturesFile($file));
    }

    /**
     * @param JsonErrorCodes|int $jsonCode
     * @param string $message
     * @param bool $encoded
     * @return array|string
     */
    public static function getJsonErrorCodeData($jsonCode, string $message, bool $encoded = true) {
        if($jsonCode instanceof JsonErrorCodes)
        {
            $jsonCode = $jsonCode->value;
        }
        if($encoded) {
            return json_encode(['message' => $message, 'code' => $jsonCode]);
        } else {
            return ['message' => $message, 'code' => $jsonCode];
        }
    }
}