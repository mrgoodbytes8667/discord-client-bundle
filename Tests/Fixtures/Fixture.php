<?php


namespace Bytes\DiscordBundle\Tests\Fixtures;


/**
 * Class Fixture
 * @package Bytes\DiscordBundle\Tests\Fixtures
 */
class Fixture
{
    /**
     * @param string $file
     * @return string
     */
    public static function getFixturesFile(string $file)
    {
        return __DIR__ . '/' . $file;
    }

    /**
     * @param string $file
     * @return string
     */
    public static function getFixturesData(string $file): string
    {
        return file_get_contents(self::getFixturesFile($file));
    }
}