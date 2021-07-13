<?php

namespace Bytes\DiscordClientBundle\Tests\Routing;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Routing\DiscordImageUrlBuilder;
use Bytes\DiscordResponseBundle\Objects\User;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Class LegacyDiscordImageUrlBuilderTest
 * @package Bytes\DiscordClientBundle\Tests\Routing
 */
class LegacyDiscordImageUrlBuilderTest extends TestCase
{
    use TestDiscordFakerTrait, ExpectDeprecationTrait;

    /**
     * @dataProvider provideHashes
     * @param $hash
     * @param $gif
     */
    public function testGetAvatarUrl($hash, $gif)
    {
        $this->expectDeprecation('Since mrgoodbytes8667/discord-client-bundle 0.1.3: This "%s" method is deprecated');
        $userId = $this->faker->userId();
        $user = new User();
        $user->setId($userId)
            ->setAvatar($hash);

        $url = DiscordImageUrlBuilder::getAvatarUrl($user);
        $this->assertEquals(sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s', $userId, $hash, $gif ? 'gif' : 'png'), $url);
    }

    /**
     * @return Generator
     */
    public function provideHashes()
    {
        $this->setupFaker();

        yield ['hash' => $this->faker->iconHash(false), 'gif' => false];
        yield ['hash' => $this->faker->iconHash(true), 'gif' => true];
    }
}