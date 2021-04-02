<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Message;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetChannelMessagesTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetChannelMessagesTest extends TestDiscordBotClientCase
{
    use TestValidateChannelMessageTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetChannelMessages()
    {
        /** @var Message[] $messages */
        $messages = $this
            ->setupResponse('HttpClient/get-channel-messages-success.json', type: '\Bytes\DiscordResponseBundle\Objects\Message[]')
            ->deserialize();

        $this->assertCount(3, $messages);

        $this->validateChannelMessage(array_shift($messages), '288732970726770486', 0,
            'Ut ea non quibusdam nam excepturi veritatis distinctio.', '249844162002927373',
            '270340838956422428', "cormier.macie", "084ef794162b84d342d76681ff679429",
            "6669", 8, null, 0, 0, true,
            true, true, true);

        $this->validateChannelMessage(array_shift($messages), '293324682303491310', 19,
            'Molestiae molestias similique recusandae voluptatem.', '236148027649769274',
            '297773459887947789', "landen.wuckert", "a_8269209c66e5686f529a0b6ba304b574",
            "2166", 6, null, 0, 0, true,
            false, false, true);
    }
}