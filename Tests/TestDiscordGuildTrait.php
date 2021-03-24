<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;


/**
 * Trait TestDiscordGuildTrait
 * @package Bytes\DiscordBundle\Tests
 *
 * @method assertNotEmpty($actual, string $message = '')
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertEmpty($actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertClassNotHasAttribute(string $attributeName, string $className, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method assertNotInstanceOf(string $expected, $actual, string $message = '')
 */
trait TestDiscordGuildTrait
{
    /**
     * @param GuildInterface|PartialGuild $guild
     * @param string $guildId
     * @param string|null $name
     * @param string|null $iconHash
     * @param string|null $ownerId
     * @param int $rolesCount
     * @param bool $withCounts
     */
    public function validateClientGetGuildAsGuild($guild, string $guildId, ?string $name, ?string $iconHash, ?string $ownerId, int $rolesCount = 0, bool $withCounts = false)
    {
        $this->assertInstanceOf(Guild::class, $guild);
        $this->assertInstanceOf(PartialGuild::class, $guild);
        $this->validateGuildSharedParts($guild, $guildId, $name, $iconHash);

        // Items only present in Guild
        $this->assertCount($rolesCount, $guild->getRoles());
        $this->assertEquals($ownerId, $guild->getOwnerId());
        if ($withCounts) {
            $this->assertNotEmpty($guild->getApproximateMemberCount());
            $this->assertNotEmpty($guild->getApproximatePresenceCount());
        } else {
            $this->assertEmpty($guild->getApproximateMemberCount());
            $this->assertEmpty($guild->getApproximatePresenceCount());
        }
    }

    /**
     * @param GuildInterface|PartialGuild $guild
     * @param string $guildId
     * @param string|null $name
     * @param string|null $iconHash
     *
     * @internal
     */
    protected function validateGuildSharedParts($guild, string $guildId, ?string $name, ?string $iconHash)
    {
        $this->assertNotEmpty($guild);

        // Items present in PartialGuild and Guild
        $this->assertEquals($guildId, $guild->getId());
        $this->assertEquals($name, $guild->getName());
        $this->assertEquals($iconHash, $guild->getIcon());

        // Items only present in PartialGuild
        $this->assertEmpty($guild->getOwner());
        $this->assertEmpty($guild->getPermissions());
    }

    /**
     * @param GuildInterface|PartialGuild $guild
     * @param string $guildId
     * @param string|null $name
     * @param string|null $iconHash
     * @param bool $withCounts
     */
    public function validateClientGetGuildAsPartialGuild($guild, string $guildId, ?string $name, ?string $iconHash, bool $withCounts = false)
    {
        $this->assertInstanceOf(PartialGuild::class, $guild);
        $this->assertNotInstanceOf(Guild::class, $guild);
        $this->validateGuildSharedParts($guild, $guildId, $name, $iconHash);

        // Items only present in PartialGuild, still won't be present if we convert down
        $this->assertEmpty($guild->getOwner());
        $this->assertEmpty($guild->getPermissions());

        // Items only present in Guild
        $this->assertClassNotHasAttribute('roles', get_class($guild));
        $this->assertClassNotHasAttribute('ownerId', get_class($guild));
        $this->assertClassNotHasAttribute('approximateMemberCount', get_class($guild));
        $this->assertClassNotHasAttribute('approximatePresenceCount', get_class($guild));
    }
}