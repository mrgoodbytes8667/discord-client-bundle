<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;


use Bytes\DiscordBundle\Tests\HttpClient\ValidateUserTrait;
use Bytes\DiscordResponseBundle\Objects\Message;

/**
 * Trait TestValidateChannelMessageTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 *
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertTrue($condition, string $message = '')
 * @method assertFalse($condition, string $message = '')
 * @method assertEmpty($actual, string $message = '')
 * @method assertShouldBeNull($expected, $actual, string $message = '')
 *
 */
trait TestValidateChannelMessageTrait
{
    use ValidateUserTrait;

    /**
     * @param Message $message
     * @param $id
     * @param $type
     * @param $content
     * @param $channelId
     * @param $authorId
     * @param $authorUsername
     * @param $authorAvatar
     * @param $authorDiscriminator
     * @param $authorFlags
     * @param $authorBot
     * @param $attachmentCount
     * @param $embedCount
     * @param $pinned
     * @param $mentionEveryone
     * @param $tts
     * @param bool $editedTimestampNull
     */
    public function validateChannelMessage($message, $id, $type, $content, $channelId, $authorId, $authorUsername, $authorAvatar, $authorDiscriminator, $authorFlags, $authorBot, $attachmentCount, $embedCount, $pinned, $mentionEveryone, $tts, $editedTimestampNull)
    {
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($content, $message->getContent());

        $this->validateUser($message->getAuthor(), $authorId, $authorUsername, $authorAvatar, $authorDiscriminator, $authorFlags, $authorBot);

        $this->assertEquals($id, $message->getId());
        $this->assertEquals($type, $message->getType());
        $this->assertEquals($channelId, $message->getChannelID());
        $this->assertEquals($pinned, $message->getPinned());
        $this->assertEquals($mentionEveryone, $message->getMentionEveryone());
        $this->assertEquals($tts, $message->getTts());
        $this->assertCount($embedCount, $message->getEmbeds());

        $this->assertCount($attachmentCount, $message->getAttachments());

        $this->assertShouldBeNull($editedTimestampNull, $message->getEditedTimestamp());
    }
}