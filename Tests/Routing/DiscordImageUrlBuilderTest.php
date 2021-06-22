<?php

namespace Bytes\DiscordClientBundle\Tests\Routing;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Routing\DiscordImageUrlBuilder;
use Bytes\DiscordResponseBundle\Objects\User;
use PHPUnit\Framework\TestCase;

/**
 * Class DiscordImageUrlBuilderTest
 * @package Bytes\DiscordClientBundle\Tests\Routing
 */
class DiscordImageUrlBuilderTest extends TestCase
{
    use TestDiscordFakerTrait;

    /**
     * @dataProvider provideHashes
     * @param $hash
     * @param $gif
     */
    public function testGetAvatarUrl($hash, $gif)
    {
        $userId = $this->faker->userId();
        $user = new User();
        $user->setId($userId)
            ->setAvatar($hash);

        $url = DiscordImageUrlBuilder::getAvatarUrl($user);
        $this->assertEquals(sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s', $userId, $hash, $gif ? 'gif' : 'png'), $url);
    }

    /**
     * @return \Generator
     */
    public function provideHashes()
    {
        $this->setupFaker();

        yield ['hash' => $this->faker->iconHash(false), 'gif' => false];
        yield ['hash' => $this->faker->iconHash(true), 'gif' => true];
    }
}