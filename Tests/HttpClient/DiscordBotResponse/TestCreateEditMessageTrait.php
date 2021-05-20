<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;


use Bytes\DiscordResponseBundle\Objects\Message;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Trait TestCreateEditMessageTrait
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 *
 * @method \Bytes\ResponseBundle\HttpClient\Response\Response setupResponse(?string $fixtureFile = null, $content = null, int $code = Response::HTTP_OK, $type = \stdClass::class, ?string $exception = null)
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertFalse($condition, string $message = '')
 * @method assertEmpty($actual, string $message = '')
 *
 */
trait TestCreateEditMessageTrait
{
    /**
     * @return \Generator
     */
    abstract public function provideFixtureFileWithoutReference(): \Generator;

    /**
     * @return \Generator
     */
    abstract public function provideFixtureFileWithReference(): \Generator;

    /**
     * @dataProvider provideFixtureFileWithoutReference
     * @param $file
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testMessage($file)
    {
        $message = $this->getMessage($file);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEmpty($message->getContent());
        $this->assertEquals('rhea17', $message->getAuthor()->getUsername());
        $this->assertEquals('234627215184254300', $message->getId());
        $this->assertEquals(0, $message->getType());
        $this->assertEquals('239800192314657438', $message->getChannelID());
        $this->assertFalse($message->getPinned());
        $this->assertFalse($message->getMentionEveryone());
        $this->assertFalse($message->getTts());
        $this->assertCount(1, $message->getEmbeds());
    }

    /**
     * @dataProvider provideFixtureFileWithReference
     * @param $file
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testMessageWithReference($file)
    {
        $message = $this->getMessage($file);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam a justo id elit pharetra dapibus non eget massa. Suspendisse pretium enim ac malesuada iaculis. Donec in mattis erat, non molestie nisl. Suspendisse aliquet laoreet mauris, quis porta ipsum convallis et. Etiam porttitor fermentum velit, eu molestie tortor aliquam eu. Ut.", $message->getContent());
        $this->assertEquals('cormier.macie', $message->getAuthor()->getUsername());
        $this->assertEquals('293324682303491310', $message->getId());
        $this->assertEquals(0, $message->getType());
        $this->assertEquals('236148027649769274', $message->getChannelID());
        $this->assertFalse($message->getPinned());
        $this->assertFalse($message->getMentionEveryone());
        $this->assertFalse($message->getTts());
        $this->assertCount(1, $message->getEmbeds());
        $this->assertInstanceOf(Message::class, $message->getReferencedMessage());
    }

    /**
     * @param $file
     * @return Message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function getMessage($file)
    {
        return $this
            ->setupResponse($file, type: Message::class)
            ->deserialize();
    }
}