<?php


namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ChannelIdInterface;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\Message\Content;
use Generator;

trait MessageProviderTrait
{
    use GuildProviderTrait, TestDiscordFakerTrait;

    /**
     * @return Generator
     */
    public function provideInvalidChannelMessage()
    {
        $message = new Message();
        $message->setId('123');
        yield ['message' => $message, 'channel' => null];

        $message = new Message();
        $message->setChannelId('123');
        yield ['message' => $message, 'channel' => null];

        foreach ($this->provideInvalidGetGuildArguments() as $value) {
            yield ['message' => $value['guild'], 'channel' => null];
        }

        $message = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $message->method('getId')
            ->willReturn('230858112993375816');

        foreach ($this->provideInvalidGetGuildArguments() as $value) {
            yield ['message' => $message, 'channel' => $value['guild']];
        }
    }

    /**
     * @return Generator
     */
    public function provideInvalidChannelValidContent()
    {
        foreach ($this->provideCreateEditMessage() as $item) {
            foreach ($this->provideInvalidGetGuildArguments() as $value) {
                yield ['channel' => $value['guild'], 'content' => $item['content'], 'tts' => $item['tts']];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideCreateEditMessage()
    {
        $this->setupFaker();

        $contents = [
            $this->faker->sentence(),
            $this->faker->embed(),
            $this->faker->embeds(),
        ];

        $content = new Content();
        $content->setContent($this->faker->sentence());
        $contents[] = $content;

        $content = new Content();
        $content->addEmbed($this->faker->embed());
        $contents[] = $content;

        $content = new Content();
        $content->setContent($this->faker->sentence());
        $content->addEmbed($this->faker->embed());
        $contents[] = $content;

        foreach ($this->provideValidChannelMessagesInternal() as $cm) {
            foreach ($this->provideBooleans() as $tts) {
                foreach ($contents as $content) {
                    yield ['channel' => $cm['channel'], 'message' => $cm['message'], 'content' => $content, 'tts' => $tts[0]];
                }
            }
        }
    }

    /**
     * @return Generator
     * @internal
     */
    public function provideValidChannelMessagesInternal()
    {
        $message = new Message();
        $message->setId('123');
        $message->setChannelID('456');

        yield ['channel' => $message, 'message' => $message];
        yield ['channel' => $message, 'message' => null];

        $message = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $message->method('getId')
            ->willReturn('230858112993375816');

        $channel = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $channel->method('getId')
            ->willReturn('230858112993375816');
        yield ['channel' => $channel, 'message' => $message];
        yield ['channel' => $channel, 'message' => null];

        $channel = $this
            ->getMockBuilder(ChannelIdInterface::class)
            ->getMock();
        $channel->method('getChannelId')
            ->willReturn('230858112993375816');
        yield ['channel' => $channel, 'message' => $message];
        yield ['channel' => $channel, 'message' => null];

        yield ['channel' => '456', 'message' => '123'];
        yield ['channel' => '456', 'message' => null];
    }

    /**
     * @return Generator
     */
    public function provideValidDeleteMessages()
    {
        $message = new Message();
        $message->setId('123');
        $message->setChannelID('456');

        yield ['message' => $message, 'channel' => $message];
        yield ['message' => $message, 'channel' => null];

        $message = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $message->method('getId')
            ->willReturn('230858112993375816');

        $channel = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $channel->method('getId')
            ->willReturn('230858112993375816');
        yield ['message' => $message, 'channel' => $channel];

        $channel = $this
            ->getMockBuilder(ChannelIdInterface::class)
            ->getMock();
        $channel->method('getChannelId')
            ->willReturn('230858112993375816');
        yield ['message' => $message, 'channel' => $channel];

        yield ['message' => '456', 'channel' => '123'];
    }
}