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
    use CommandProviderTrait, ClientExceptionResponseProviderTrait, WebhookProviderTrait, ValidateUserTrait, TestEmptyResponseTrait {
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
}