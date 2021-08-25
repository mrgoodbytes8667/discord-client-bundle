<?php

namespace Bytes\DiscordClientBundle\Tests\Event\Message;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Enums\MessageType;
use Bytes\DiscordResponseBundle\Objects\Application;
use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\Member;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\Message\AllowedMentions;
use Bytes\DiscordResponseBundle\Objects\MessageReference;
use Bytes\DiscordResponseBundle\Objects\Slash\MessageInteraction;
use Exception;
use Generator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

/**
 *
 */
trait MessageEventProviderTrait
{
    use TestDiscordFakerTrait;

    /**
     * @return Generator
     */
    public function provideMessageReference()
    {
        yield [MessageReference::create('123', '456', '789')];
    }

    /**
     * @return Generator
     */
    public function provideThread()
    {
        yield [new Channel()];
    }

    /**
     * @return Generator
     */
    public function provideComponents()
    {
        yield ['count' => 1, 'components' => [new Message\Component()]];
        yield ['count' => 5, 'components' => [new Message\Component(), new Message\Component(), new Message\Component(), new Message\Component(), new Message\Component()]];
    }

    /**
     * @return Generator
     */
    public function provideEntityIds()
    {
        $this->setupFaker();
        yield [$this->faker->randomDigit()];
        yield [1];
        yield [999999999999];
        yield [Uuid::v1()];
        yield [Uuid::v4()];
        yield [new Ulid()];
        yield [$this->faker->uuid()];
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function provideFullCreateMessage()
    {
        $this->setupFaker();

        $author = $this->faker->user();
        $member = new Member();
        $tts = $this->faker->boolean();

        yield [
            'guild_id' => $this->faker->guildId(),
            'id' => $this->faker->messageId(),
            'channelID' => $this->faker->channelId(),
            'author' => $author,
            'member' => $member,
            'content' => $this->faker->sentence(),
            'timestamp' => $this->faker->dateTime(),
            'editedTimestamp' => null,
            'tts' => $tts,
            'mentionEveryone' => $this->faker->boolean(),
            'mentions' => [$author],
            'mentionRoles' => [$this->faker->roleId()],
            'mentionChannels' => [$this->faker->channelMention()],
            'attachments' => [],
            'embeds' => $this->faker->embeds(),
            'reactions' => $this->faker->reactions(),
            'nonce' => $this->faker->word(),
            'pinned' => $this->faker->boolean(),
            'webhookId' => $this->faker->snowflake(),
            'type' => $this->faker->randomEnumValue(MessageType::class),
            'activity' => $this->faker->word(),
            'application' => new Application(),
            'messageReference' => $this->faker->messageReference(),
            'flags' => $this->faker->randomDigit(),
            'referencedMessage' => null,
            'interaction' => new MessageInteraction(),
            'thread' => $this->faker->channel(),
            'components' => $this->faker->componentActionRows(),
            'stickerItems' => [],
        ];
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function provideFullEditMessage()
    {
        $this->setupFaker();

        $author = $this->faker->user();
        $member = new Member();
        $tts = $this->faker->boolean();

        yield [
            'guild_id' => $this->faker->guildId(),
            'id' => $this->faker->messageId(),
            'channelID' => $this->faker->channelId(),
            'author' => $author,
            'member' => $member,
            'content' => $this->faker->sentence(),
            'timestamp' => $this->faker->dateTime(),
            'editedTimestamp' => $this->faker->dateTime(),
            'tts' => $tts,
            'mentionEveryone' => $this->faker->boolean(),
            'mentions' => [$author],
            'mentionRoles' => [$this->faker->roleId()],
            'mentionChannels' => [$this->faker->channelMention()],
            'attachments' => [],
            'embeds' => $this->faker->embeds(),
            'reactions' => $this->faker->reactions(),
            'nonce' => $this->faker->word(),
            'pinned' => $this->faker->boolean(),
            'webhookId' => $this->faker->snowflake(),
            'type' => $this->faker->randomEnumValue(MessageType::class),
            'activity' => $this->faker->word(),
            'application' => new Application(),
            'messageReference' => $this->faker->messageReference(),
            'flags' => $this->faker->randomDigit(),
            'referencedMessage' => null,
            'interaction' => new MessageInteraction(),
            'thread' => $this->faker->channel(),
            'components' => $this->faker->componentActionRows(),
            'stickerItems' => [],
        ];
    }
}