<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\DiscordBundle\HttpClient\DiscordResponse;
use Bytes\DiscordBundle\Tests\ClientExceptionResponseProviderTrait;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordResponseBundle\Enums\MessageType;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Trait TestDiscordResponseTrait
 * @package Bytes\DiscordBundle\Tests\HttpClient
 *
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method expectException(string $exception)
 * @method expectExceptionMessage(string $message)
 * @method setupClient(HttpClientInterface $httpClient)
 * @method DiscordResponse setupResponse(?string $fixtureFile = null, $content = null, int $code = Response::HTTP_OK, $type = \stdClass::class, ?string $exception = null)
 * @property SerializerInterface $serializer
 */
trait TestDiscordResponseTrait
{
    use CommandProviderTrait, ClientExceptionResponseProviderTrait, WebhookProviderTrait, TestEmptyResponseTrait {
        TestEmptyResponseTrait::testSuccess as testDeleteWebhookMessage;
        TestEmptyResponseTrait::testSuccessInvalidReturnCode as testDeleteWebhookMessageInvalidReturnCode;
    }

    /**
     *
     */
    public function testGetGuilds()
    {
        $guilds = $this->setupResponse('HttpClient/get-guilds.json', type: '\Bytes\DiscordResponseBundle\Objects\PartialGuild[]')->deserialize();

        $this->assertCount(2, $guilds);
        $this->assertInstanceOf(PartialGuild::class, $guilds[0]);
        $this->assertInstanceOf(PartialGuild::class, $guilds[1]);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testResponseFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $test = $this->setupResponse(code: $code);

        $test->deserialize();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetMe()
    {
        $user = $this->setupResponse('HttpClient/get-me.json', type: User::class)->deserialize();
        $this->validateUser($user, '272930239796055326', 'elvie70', 'cba426068ee1c51edab2f0c38549f4bc', '6793', 0, true);
    }

    /**
     * @param $user
     * @param $id
     * @param $username
     * @param $avatar
     * @param $discriminator
     * @param $flags
     * @param $bot
     */
    protected function validateUser($user, $id, $username, $avatar, $discriminator, $flags, $bot)
    {
        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($avatar, $user->getAvatar());
        $this->assertEquals($discriminator, $user->getDiscriminator());
        $this->assertEquals($flags, $user->getPublicFlags());
        $this->assertEquals($bot, $user->getBot());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testExecuteWebhookWait()
    {
        /** @var Message $message */
        $message = $this->setupResponse('HttpClient/execute-webhook-success.json', type: Message::class)->deserialize();

        $this->validateWebhookMessage($message, "487682468505944112", MessageType::default(), "Hello, World!",
            "246703651155663276", true, "453971306226180868", "Spidey Bot", null, "0000", null, 0, 1, 0, 0, false,
            false, false, true, false, 0, "829350728622800916");
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testExecuteWebhookNoWaitWillNotDeserialize()
    {
        $this->expectException(NotEncodableValueException::class);
        $this->setupResponse(code: Response::HTTP_NO_CONTENT)->deserialize();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditWebhookMessage()
    {
        /** @var Message $message */
        $message = $this->setupResponse('HttpClient/edit-webhook-message-success.json', type: Message::class)->deserialize();

        $this->validateWebhookMessage($message, "487682468505944112", MessageType::default(), "Hello, World!",
            "246703651155663276", true, "453971306226180868", "Spidey Bot", null, "0000", null, 0, 1, 0, 0, false,
            false, false, true, true, 0, "829350728622800916");
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
                                              ?string $msgRefChannelId = null, ?string $msgRefGuildId = null, ?string $msgRefMessageId = null)
    {
        $this->assertInstanceOf(Message::class, $message);

        $this->assertEquals($id, $message->getId());
        $this->assertEquals($type->value, $message->getType());
        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($channelId, $message->getChannelId());

        // Author
        $this->validateUser($message->getAuthor(), $authorId, $authorUsername, $authorAvatar, $authorDiscriminator, $authorFlags, $authorBot);

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
    }
}