<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\Emojis;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ChannelIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use DateTime;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordBotClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
class DiscordBotClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait, CommandProviderTrait;

    /**
     * @requires PHPUnit >= 9
     */
    public function testCreateCommand()
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED),
            MockJsonResponse::makeFixture('HttpClient/edit-command-success.json'),
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED),
            MockJsonResponse::makeFixture('HttpClient/edit-command-success.json')));

        $b = $client->createCommand(Sample::createCommand());
        $this->assertResponseIsSuccessful($b);
        $this->assertResponseStatusCodeSame($b, Response::HTTP_CREATED);

        $c = $client->createCommand(Sample::createCommand());
        $this->assertResponseIsSuccessful($c);
        $this->assertResponseStatusCodeSame($c, Response::HTTP_OK);

        $stub = $this->createStub(PartialGuild::class);
        $stub->setId('123');

        $b = $client->createCommand(Sample::createCommand(), $stub);
        $this->assertResponseIsSuccessful($b);
        $this->assertResponseStatusCodeSame($b, Response::HTTP_CREATED);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordBotClient
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordBotClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandInvalidDescription()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $response = $client->createCommand(ApplicationCommand::create('invalid', 'I am valid input that will be treated as being over 100 characters'));
        $this->assertResponseStatusCodeSame($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandInvalidDescriptionValidatorError()
    {
        $faker = self::getFaker();
        $description = '';
        do {
            $description .= $faker->paragraph();
        } while (strlen($description) < 1000);

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $this->expectException(ValidatorException::class);
        $client->createCommand(ApplicationCommand::create('invalid', $description));
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetCommands()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-commands-success.json'),
        ]));

        $response = $client->getCommands();

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-commands-success.json'));
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     */
    public function testGetCommandsFailure($cmd, ?IdInterface $guild, int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));
        $response = $client->getCommands($guild);

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     */
    public function testGetCommand($cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-command-success.json'),
        ]));

        $response = $client->getCommand($cmd, $guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-command-success.json'));
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     */
    public function testGetCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));
        $response = $client->getCommand($cmd, $guild);

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideInvalidCommandAndValidGuild
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetCommandBadCommandArgument($cmd, $guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getCommand($cmd, $guild);
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommand($cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteCommand($cmd, $guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->deleteCommand($cmd, $guild);
    }

    /**
     *
     */
    public function testDeleteCommandFailureBadCommandArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteCommand(null, null);
    }

    /**
     * @dataProvider provideValidGetGuildFixtureFiles
     */
    public function testGetGuild(string $file)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getGuild('737645596567095093');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    public function provideValidGetGuildFixtureFiles()
    {
        yield ['file' => 'HttpClient/get-guild-success.json'];
        yield ['file' => 'HttpClient/get-guild-with-counts-success.json'];
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetGuildBadGuildArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getGuild($guild);
    }

    public function provideInvalidGetGuildArguments()
    {
        yield ['guild' => ''];
        yield ['guild' => null];
        yield ['guild' => new DateTime()];
        yield ['guild' => []];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetGuildFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getGuild('737645596567095093');
    }

    /**
     * @dataProvider provideValidGetChannelsFixtureFiles
     */
    public function testGetChannels(string $file, $guildId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getChannels($guildId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    /**
     * @return \Generator
     * @todo Remove v6
     */
    public function provideValidGetChannelsFixtureFiles()
    {
        foreach([6, 8] as $apiVersion) {
            $file = sprintf('HttpClient/get-channels-v%d-success.json', $apiVersion);
            $mock = $this
                ->getMockBuilder(GuildIdInterface::class)
                ->getMock();
            $mock->method('getGuildId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'guildId' => $mock];
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'guildId' => $mock];
            yield ['file' => $file, 'guildId' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelsFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannels('737645596567095093');
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetChannelsBadChannelsArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannels($guild);
    }

    /**
     * @dataProvider provideValidGetChannelFixtureFiles
     */
    public function testGetChannel(string $file, $channelId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getChannel($channelId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    /**
     * @return \Generator
     * @todo Remove v6
     */
    public function provideValidGetChannelFixtureFiles()
    {
        foreach([6, 8] as $apiVersion) {
            $file = sprintf('HttpClient/get-channel-v%d-success.json', $apiVersion);
            $mock = $this
                ->getMockBuilder(ChannelIdInterface::class)
                ->getMock();
            $mock->method('getChannelId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'channelId' => $mock];
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'channelId' => $mock];
            yield ['file' => $file, 'channelId' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannel('737645596567095093');
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetChannelBadChannelArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannel($guild);
    }

    /**
     * @dataProvider provideValidChannelMessage
     */
    public function testGetChannelMessage($message, $channel)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-message-success.json'),
        ]));

        $response = $client->getChannelMessage($message, $channel);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-message-success.json'));
    }

    /**
     * @return \Generator
     */
    public function provideValidChannelMessage()
    {
        $message = new Message();
        $message->setId('123');
        $message->setChannelID('456');
        yield ['message' => $message, 'channel' => null];
        yield ['message' => $message, 'channel' => '']; // Still valid since channel is ignored here

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
        yield ['message' => '123', 'channel' => '456'];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannelMessage('245963893292923965', '737645596567095093');
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetChannelMessageBadChannelArgument($message, $channel)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannelMessage($message, $channel);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidChannelMessage()
    {
        $message = new Message();
        $message->setId('123');
        yield ['message' => $message, 'channel' => null];

        $message = new Message();
        $message->setChannelId('123');
        yield ['message' => $message, 'channel' => null];

        foreach($this->provideInvalidGetGuildArguments() as $value)
        {
            yield ['message' => $value['guild'], 'channel' => null];
        }

        $message = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $message->method('getId')
            ->willReturn('230858112993375816');

        foreach($this->provideInvalidGetGuildArguments() as $value)
        {
            yield ['message' => $message, 'channel' => $value['guild']];
        }
    }

    /**
     * @dataProvider provideValidChannelMessages
     */
    public function testGetChannelMessages($channel, $filter, $message, $limit)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-messages-success.json'),
        ]));

        $response = $client->getChannelMessages($channel, $filter, $message, $limit);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-messages-success.json'));
    }

    public function provideValidChannelMessages()
    {
        $faker = self::getFaker();

        foreach ($this->provideValidChannelMessagesInternal() as $cm) {
            foreach ([-1, 0, 1, 10, 50, 90, 99, 100, 101, null] as $limit) {
                foreach($faker->filter() as $filter) {
                    yield ['channel' => $cm['channel'], 'filter' => empty($cm['message']) ? null : $filter, 'message' => $cm['message'], 'limit' => $limit];
                }
            }
        }

    }

    /**
     * @return \Generator
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
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelMessagesFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannelMessages('245963893292923965');
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testGetChannelMessagesBadChannelArgument($message, $channel)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannelMessages($channel);
    }

    /**
     * @dataProvider provideCreateEditMessage
     */
    public function testCreateMessage($channel, $message, $content, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-messages-success.json'),
        ]));

        $response = $client->createMessage($channel, $content, $tts);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-messages-success.json'));
    }

    /**
     * @return \Generator
     */
    public function provideCreateEditMessage()
    {
        $faker = self::getFaker();

        $contents = [
            $faker->sentence(),
            $faker->embed(),
            $faker->embeds(),
        ];

        foreach ($this->provideValidChannelMessagesInternal() as $cm)
        {
            foreach ($contents as $content)
            {
                yield ['channel' => $cm['channel'], 'message' => $cm['message'], 'content' => $content, 'tts' => true];
                yield ['channel' => $cm['channel'], 'message' => $cm['message'], 'content' => $content, 'tts' => false];
            }

            $content = new Message\Content();
            $content->setContent($faker->sentence());
            $contents[] = $content;

            $content = new Message\Content();
            $content->setEmbed($faker->embed());
            $contents[] = $content;

            $content = new Message\Content();
            $content->setContent($faker->sentence());
            $content->setEmbed($faker->embed());
            $contents[] = $content;

            foreach($contents as $content) {
                foreach ($this->provideBooleans() as $tts) {
                    yield ['channel' => $cm['channel'], 'message' => $cm['message'], 'content' => $content, 'tts' => $tts[0]];
                }
            }
        }
    }

    /**
     * @dataProvider provideInvalidChannelValidContent
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testCreateMessageBadChannelArgument($channel, $content, $tts)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createMessage($channel, $content, $tts);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidChannelValidContent()
    {
        foreach($this->provideCreateEditMessage() as $item)
        {
            foreach($this->provideInvalidGetGuildArguments() as $value)
            {
                yield ['channel' => $value['guild'], 'content' => $item['content'], 'tts' => $item['tts']];
            }
        }
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testCreateMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->createMessage('123', '123');
    }

    /**
     * @dataProvider provideCreateEditMessage
     */
    public function testEditMessage($channel, $message, $content, $tts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-channel-messages-success.json'),
        ]));

        $response = $client->editMessage($channel, $message, $content);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-channel-messages-success.json'));
    }

    /**
     * @dataProvider provideInvalidChannelValidContent
     * @param $channel
     * @param $content
     * @param $tts
     * @throws TransportExceptionInterface
     */
    public function testEditMessageBadChannelArgument($channel, $content, $tts)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->editMessage($channel, '456', $content);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testEditMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->editMessage('123', '456', 'content');
    }

    /**
     * @dataProvider provideValidDeleteMessages
     */
    public function testDeleteMessage($message, $channel)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteMessage($message, $channel);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @return \Generator
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

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testDeleteMessageBadChannelArgument($message, $channel)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannelMessages($channel);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testDeleteMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->deleteMessage('123', '123');
    }

    /**
     * @dataProvider provideValidGuilds
     */
    public function testLeaveGuild($guildId)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->leaveGuild($guildId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @return \Generator
     */
    public function provideValidGuilds()
    {
        $mock = $this
            ->getMockBuilder(GuildIdInterface::class)
            ->getMock();
        $mock->method('getGuildId')
            ->willReturn('230858112993375816');
        yield [$mock];

        $mock = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $mock->method('getId')
            ->willReturn('230858112993375816');
        yield [$mock];

        yield ['230858112993375816'];
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testLeaveGuildBadGuildArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->leaveGuild($guild);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testLeaveGuildUnknownGuild()
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/leave-guild-failure-unknown-guild.json', Response::HTTP_NOT_FOUND)
        ));

        $response = $client->leaveGuild('123');

        $this->assertResponseStatusCodeSame($response, Response::HTTP_NOT_FOUND);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/leave-guild-failure-unknown-guild.json'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_NOT_FOUND));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testLeaveGuildFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->leaveGuild('123');
    }

    /**
     * @dataProvider provideValidGetGuildMembers
     */
    public function testGetGuildMember($guild, $user)
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/get-guild-member-success.json')
        ));

        $response = $client->getGuildMember($guild, $user);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-guild-member-success.json'));
    }

    /**
     * @return \Generator
     */
    public function provideValidGetGuildMembers()
    {
        foreach($this->provideValidGuilds() as $guild)
        {
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');

            yield['guild' => $guild[0], 'user' => $mock];
            yield['guild' => $guild[0], 'user' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideInvalidGetGuildMembers
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetGuildMemberBadGuildArgument($guild, $user)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getGuildMember($guild, $user);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidGetGuildMembers()
    {
        foreach($this->provideInvalidGetGuildArguments() as $guild)
        {
            foreach($this->provideInvalidGetGuildArguments() as $user)
            {
                yield ['guild' => $guild['guild'], 'user' => $user['guild']];
            }
        }
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testGetGuildMemberJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->getGuildMember('123', '456');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @return \Generator
     */
    public function provideJsonErrorCodes()
    {
        yield ['jsonCode' => JsonErrorCodes::MISSING_ACCESS(), 'message' => 'Missing Access', 'httpCode' => Response::HTTP_FORBIDDEN];
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_GUILD(), 'message' => 'Unknown Guild', 'httpCode' => Response::HTTP_NOT_FOUND];
        yield ['jsonCode' => JsonErrorCodes::GENERAL_ERROR(), 'message' => '401: Unauthorized', 'httpCode' => Response::HTTP_UNAUTHORIZED];
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_EMOJI(), 'message' => 'Unknown Emoji', 'httpCode' => Response::HTTP_BAD_REQUEST];
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetGuildMemberFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getGuildMember('123', '456');
    }

    /**
     * @dataProvider provideValidGuilds
     */
    public function testGetGuildRoles($guildId)
    {
        $client = $this->setupClient(MockClient::requests(MockJsonResponse::makeFixture('HttpClient/get-guild-roles-success.json')));

        $response = $client->getGuildRoles($guildId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-guild-roles-success.json'));
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetGuildRolesBadGuildArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getGuildRoles($guild);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testGetGuildRolesJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->getGuildRoles('123');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetGuildRolesFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getGuildRoles('123');
    }

    /**
     * @dataProvider provideValidCreateGuildRole
     */
    public function testCreateGuildRole($guild, $name, $permissions, $color, $hoist, $mentionable)
    {
        $client = $this->setupClient(MockClient::requests(MockJsonResponse::makeFixture('HttpClient/create-guild-role-success.json')));

        $response = $client->createGuildRole($guild, $name, $permissions, $color, $hoist, $mentionable);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/create-guild-role-success.json'));
    }

    /**
     * @return \Generator
     */
    public function provideValidCreateGuildRole()
    {
        $faker = self::getFaker();

        foreach($this->provideValidGuilds() as $guild)
        {
            foreach([$faker->words(3, true), null] as $name)
            {
                foreach([Permissions::SEND_MESSAGES()->value, '2147483648', '2292252672', '0', $faker->permissionInteger(), null] as $permission) {
                    foreach([$faker->embedColor(), null] as $color) {
                        foreach($this->provideBooleansAndNull() as $hoist) {
                            foreach($this->provideBooleansAndNull() as $mentionable) {
                                yield ['guild' => $guild[0], 'name' => $name, 'permissions' => $permission, 'color' => $color, 'hoist' => $hoist[0], 'mentionable' => $mentionable[0]];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return \Generator
     */
    public function provideBooleans()
    {
        yield [true];
        yield [false];
    }

    /**
     * @return \Generator
     */
    public function provideBooleansAndNull()
    {
        yield [true];
        yield [false];
        yield [null];
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testCreateGuildRoleBadGuildArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createGuildRole($guild);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testCreateGuildRoleJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->createGuildRole('123');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testCreateGuildRoleFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->createGuildRole('123');
    }

    /**
     * @dataProvider provideValidCreateReaction
     */
    public function testCreateReaction($message, $channel, $emoji)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->createReaction($message, $emoji, $channel);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @return \Generator
     */
    public function provideValidCreateReaction()
    {
        $faker = self::getFaker();

        foreach($this->provideValidDeleteMessages() as $message) {
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $faker->globalEmoji()];
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $faker->customEmoji()];
            yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $faker->emoji()];
        }
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     *
     * @todo Clean this up
     */
    public function testCreateReactionBadGuildArgument($guild)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createReaction($guild, Emojis::sportsGamesHobbiesSoccerBall()->value, $guild);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testCreateReactionJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->createReaction('123', self::getRandomEmoji(), '456');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testCreateReactionFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->createReaction('123', self::getRandomEmoji(), '456');
    }

    /**
     * @dataProvider provideValidGetReaction
     * @param $message
     * @param $channel
     * @param $emoji
     * @param $before
     * @param $after
     * @param $limit
     */
    public function testGetReactions($message, $channel, $emoji, $before, $after, $limit)
    {
        $client = $this->setupClient(MockClient::requests(MockJsonResponse::makeFixture('HttpClient/get-reactions-success.json')));

        $response = $client->getReactions($message, $emoji, $channel, $before, $after, $limit);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-reactions-success.json'));
    }

    /**
     * @return \Generator
     */
    public function provideValidGetReaction()
    {
        $faker = self::getFaker();

        foreach($this->provideValidCreateReaction() as $message) {
            foreach([$faker->userId(), null] as $before) {
                foreach([$faker->userId(), null] as $after) {
                    foreach ([-1, 0, 1, 10, 50, 90, 99, 100, 101, null] as $limit) {
                        yield ['message' => $message['message'], 'channel' => $message['channel'], 'emoji' => $message['emoji'], 'before' => $before, 'after' => $after, 'limit' => $limit];
                    }
                }
            }

        }
    }

    /**
     * @dataProvider provideInvalidChannelMessage
     * @param $message
     * @param $channel
     * @throws TransportExceptionInterface
     */
    public function testGetReactionsBadGuildArgument($message, $channel)
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getReactions($message, self::getRandomEmoji(), $channel);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function testGetReactionsJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $response = $client->getReactions('123', self::getRandomEmoji(), '456');

        $this->assertResponseStatusCodeSame($response, $httpCode);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData($jsonCode, $message));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $response->getContent();
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetReactionsFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getReactions('123', self::getRandomEmoji(), '456');
    }

    /**
     * @return Discord|Generator|MiscProvider
     */
    protected static function getFaker()
    {
        /** @var Generator|Discord $faker */
        $faker = Factory::create();
        $faker->addProvider(new Discord($faker));

        return $faker;
    }

    /**
     * @return string
     */
    protected static function getRandomEmoji()
    {
        return self::getFaker()->emoji();
    }

}
