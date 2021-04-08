<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Enums\InteractionType;
use Bytes\DiscordResponseBundle\Enums\MessageType;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\Message\WebhookContent;
use Generator;

/**
 * Trait WebhookProviderTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
trait WebhookProviderTrait
{
    use TestDiscordFakerTrait, ValidateUserTrait;

    /**
     * @return Generator
     */
    public function provideWebhookArgs()
    {
        $this->setupFaker();

        yield ['id' => $this->faker->snowflake(), 'token' => $this->faker->accessToken(), 'content' => 'Hello, World!', 'embeds' => Embed::create('Hello, Embed!', description: 'This is an embedded message.'), 'allowedMentions' => null, 'username' => null, 'avatarUrl' => null, 'tts' => false];
        yield ['id' => $this->faker->snowflake(), 'token' => $this->faker->accessToken(), 'content' => WebhookContent::create(Embed::create('Hello, Embed!', description: 'This is an embedded message.'), content: 'Hello, World!', tts: false), 'embeds' => null, 'allowedMentions' => null, 'username' => null, 'avatarUrl' => null, 'tts' => false];
    }

    /**
     * @param Message $message
     * @param string|null $id
     * @param MessageType|null $type
     * @param string|null $content
     * @param string|null $channelId
     * @param bool|null $authorBot
     * @param string|null $authorId
     * @param string|null $authorUsername
     * @param string|null $authorAvatar
     * @param string|null $authorDiscriminator
     * @param int|null $authorFlags
     * @param int|null $attachmentCount
     * @param int|null $embedCount
     * @param int|null $mentionCount
     * @param int|null $mentionRolesCount
     * @param bool|null $pinned
     * @param bool|null $mentionEveryone
     * @param bool|null $tts
     * @param bool|null $hasTimestamp
     * @param bool|null $hasEditedTimestamp
     * @param int|null $flags
     * @param string|null $webhookId
     * @param string|null $msgRefChannelId
     * @param string|null $msgRefGuildId
     * @param string|null $msgRefMessageId
     */
    protected function validateWebhookMessage($message, ?string $id, ?MessageType $type, ?string $content, ?string $channelId,
                                              ?bool $authorBot, ?string $authorId, ?string $authorUsername, ?string $authorAvatar,
                                              ?string $authorDiscriminator, ?int $authorFlags, ?int $attachmentCount, ?int $embedCount, ?int $mentionCount,
                                              ?int $mentionRolesCount, ?bool $pinned, ?bool $mentionEveryone, ?bool $tts,
                                              ?bool $hasTimestamp, ?bool $hasEditedTimestamp, ?int $flags, ?string $webhookId,
                                              ?string $msgRefChannelId = null, ?string $msgRefGuildId = null, ?string $msgRefMessageId = null,
                                              ?string $interactionId = null, ?InteractionType $interactionType = null, ?string $interactionName = null,
                                              ?bool $interactionUserBot = null, ?string $interactionUserId = null,
                                              ?string $interactionUserUsername = null, ?string $interactionUserAvatar = null,
                                              ?string $interactionUserDiscriminator = null, ?int $interactionUserFlags = null)
    {
        $this->assertInstanceOf(Message::class, $message);

        $this->assertEquals($id, $message->getId());
        $this->assertEquals($type?->value, $message->getType());
        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($channelId, $message->getChannelId());

        // Author
        $this->validateUser($message->getAuthor(), $authorId, $authorUsername, $authorAvatar, $authorDiscriminator,
            $authorFlags, $authorBot);

        $this->assertCount($attachmentCount, $message->getAttachments());

        // Embeds
        $this->assertCount($embedCount, $message->getEmbeds());

        $this->assertCount($mentionCount, $message->getMentions());
        $this->assertCount($mentionRolesCount, $message->getMentionRoles());
        $this->assertEquals($pinned, $message->getPinned());
        $this->assertEquals($mentionEveryone, $message->getMentionEveryone());
        $this->assertEquals($tts, $message->getTts());
        $this->assertEquals($hasTimestamp, !empty($message->getTimestamp()));
        $this->assertEquals($hasEditedTimestamp, !empty($message->getEditedTimestamp()));
        $this->assertEquals($flags, $message->getFlags());
        $this->assertEquals($webhookId, $message->getWebhookId());

        $this->assertEquals($msgRefChannelId, $message->getMessageReference()?->getChannelID());
        $this->assertEquals($msgRefGuildId, $message->getMessageReference()?->getGuildId());
        $this->assertEquals($msgRefMessageId, $message->getMessageReference()?->getMessageId());

        $this->assertEquals($interactionId, $message->getInteraction()?->getId());
        $this->assertEquals($interactionType, $message->getInteraction()?->getType());
        $this->assertEquals($interactionName, $message->getInteraction()?->getName());

        $this->validateUser($message->getInteraction()?->getUser(), $interactionUserId, $interactionUserUsername,
            $interactionUserAvatar, $interactionUserDiscriminator, $interactionUserFlags, $interactionUserBot);
    }
}