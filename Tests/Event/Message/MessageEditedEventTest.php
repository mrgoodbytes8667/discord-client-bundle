<?php

namespace Bytes\DiscordClientBundle\Tests\Event\Message;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Event\Message\MessageEditedEvent;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\Tests\Common\DataProvider\NullProviderTrait;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class MessageEditedEventTest extends TestCase
{
    use TestDiscordFakerTrait, NullProviderTrait, MessageEventProviderTrait;

    /**
     * @dataProvider provideMessageReference
     */
    public function testGetSetMessageReference($ref)
    {
        $message = new MessageEditedEvent();
        $this->assertNull($message->getMessageReference());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setMessageReference(null));
        $this->assertNull($message->getMessageReference());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setMessageReference($ref));
        $this->assertEquals($ref, $message->getMessageReference());
    }

    /**
     * @dataProvider provideThread
     * @dataProvider provideNull
     * @param $thread
     */
    public function testGetSetThread($thread)
    {
        $message = new MessageEditedEvent();
        $this->assertNull($message->getThread());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setThread(null));
        $this->assertNull($message->getThread());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setThread($thread));
        $this->assertEquals($thread, $message->getThread());
    }

    /**
     * @dataProvider provideComponents
     * @param $count
     * @param $components
     */
    public function testGetSetComponents($count, $components)
    {
        $message = new MessageEditedEvent();
        $this->assertNull($message->getComponents());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setComponents(null));
        $this->assertNull($message->getComponents());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setComponents($components));
        $this->assertCount($count, $message->getComponents());
        $this->assertEquals($components, $message->getComponents());
    }

    /**
     * @dataProvider provideEntityIds
     * @dataProvider provideNull
     * @param $entityId
     */
    public function testGetSetEntityId($entityId)
    {
        $message = new MessageEditedEvent();
        $this->assertNull($message->getEntityId());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setEntityId(null));
        $this->assertNull($message->getEntityId());
        $this->assertInstanceOf(MessageEditedEvent::class, $message->setEntityId($entityId));
        $this->assertEquals($entityId, $message->getEntityId());
    }

    /**
     * @dataProvider provideFullEditMessage
     * @param $guild_id
     * @param $id
     * @param $channelID
     * @param $author
     * @param $member
     * @param $content
     * @param $timestamp
     * @param $editedTimestamp
     * @param $tts
     * @param $mentionEveryone
     * @param $mentions
     * @param $mentionRoles
     * @param $mentionChannels
     * @param $attachments
     * @param $embeds
     * @param $reactions
     * @param $nonce
     * @param $pinned
     * @param $webhookId
     * @param $type
     * @param $activity
     * @param $application
     * @param $messageReference
     * @param $flags
     * @param $referencedMessage
     * @param $interaction
     * @param $thread
     * @param $components
     * @param $stickerItems
     * @throws Exception
     */
    public function testCreateFromMessage($guild_id, $id, $channelID, $author, $member, $content, $timestamp, $editedTimestamp, $tts, $mentionEveryone, $mentions, $mentionRoles, $mentionChannels, $attachments, $embeds, $reactions, $nonce, $pinned, $webhookId, $type, $activity, $application, $messageReference, $flags, $referencedMessage, $interaction, $thread, $components, $stickerItems)
    {
        $message = new Message();
        $message->setAuthor($author)
            ->setMember($member)
            ->setContent($content)
            ->setTimestamp($timestamp)
            ->setEditedTimestamp($editedTimestamp)
            ->setTts($tts)
            ->setMentionEveryone($mentionEveryone)
            ->setMentionRoles($mentionRoles)
            ->setMentionChannels($mentionChannels)
            ->setEmbeds($embeds)
            ->setReactions($reactions)
            ->setNonce($nonce)
            ->setPinned($pinned)
            ->setWebhookId($webhookId)
            ->setType($type)
            ->setMessageReference($messageReference)
            ->setFlags($flags)
            ->setReferencedMessage($referencedMessage)
            ->setInteraction($interaction)
            ->setThread($thread)
            ->setComponents($components)
            ->setStickerItems($stickerItems)
            ->setId($id)
            ->setGuildId($guild_id)
            ->setChannelID($channelID);
        $event = MessageEditedEvent::createFromMessage($message);

        $this->assertEquals($author, $event->getAuthor());
        $this->assertEquals($member, $event->getMember());
        $this->assertEquals($content, $event->getContent());
        $this->assertEquals($timestamp, $event->getTimestamp());
        $this->assertEquals($editedTimestamp, $event->getEditedTimestamp());
        $this->assertEquals($tts, $event->getTts());
        $this->assertEquals($mentionEveryone, $event->getMentionEveryone());
        $this->assertEquals($mentionRoles, $event->getMentionRoles());
        $this->assertEquals($mentionChannels, $event->getMentionChannels());
        $this->assertEquals($embeds, $event->getEmbeds());
        $this->assertEquals($reactions, $event->getReactions());
        $this->assertEquals($nonce, $event->getNonce());
        $this->assertEquals($pinned, $event->getPinned());
        $this->assertEquals($webhookId, $event->getWebhookId());
        $this->assertEquals($type, $event->getType());
        $this->assertEquals($messageReference, $event->getMessageReference());
        $this->assertEquals($flags, $event->getFlags());
        $this->assertEquals($referencedMessage, $event->getReferencedMessage());
        $this->assertEquals($interaction, $event->getInteraction());
        $this->assertEquals($thread, $event->getThread());
        $this->assertEquals($components, $event->getComponents());
        $this->assertEquals($stickerItems, $event->getStickerItems());
        $this->assertEquals($id, $event->getId());
        $this->assertEquals($guild_id, $event->getGuildId());
        $this->assertEquals($channelID, $event->getChannelID());
    }

    /**
     * @throws Exception
     */
    public function testCreateWithNoMessage()
    {
        $event = MessageEditedEvent::createFromMessage(null);

        $this->assertNull($event->getGuildId());
    }
}