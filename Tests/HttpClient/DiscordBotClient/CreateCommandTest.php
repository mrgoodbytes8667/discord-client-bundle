<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonTooManyRetriesResponse;
use Bytes\DiscordResponseBundle\Objects\Application\Command\ChatInputCommand;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateCommandTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class CreateCommandTest extends TestDiscordBotClientCase
{
    use TestDiscordFakerTrait, GuildProviderTrait;

    /**
     * @dataProvider provideCreateCommand
     * @param $command
     * @param $guild
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateCommand($command, $guild) {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED)));

        $cmd = $client->createCommand($command, $guild);
        $this->assertResponseIsSuccessful($cmd);
        $this->assertResponseStatusCodeSame($cmd, Response::HTTP_CREATED);
    }

    /**
     * @dataProvider provideEditCommand
     * @param $command
     * @param $guild
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditCommand($command, $guild) {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/edit-command-success.json', Response::HTTP_OK)));

        $cmd = $client->createCommand($command, $guild);
        $this->assertResponseIsSuccessful($cmd);
        $this->assertResponseStatusCodeSame($cmd, Response::HTTP_OK);
    }

    /**
     * @return \Generator
     */
    public function provideCreateCommand()
    {
        foreach($this->provideValidGuilds() as $guild) {
            yield ['command' => Sample::createCommand(), 'guild' => $guild[0]];
            yield ['command' => function () {
                return Sample::createCommand();
            }, 'guild' => $guild[0]];
        }
    }

    /**
     * @return \Generator
     */
    public function provideEditCommand()
    {
        foreach($this->provideValidGuilds() as $guild) {
            yield ['command' => Sample::createCommand(), 'guild' => $guild[0]];
            yield ['command' => function () {
                $c = Sample::createCommand();
                $c->setId('123');
                return $c;
            }, 'guild' => $guild[0]];
        }
    }

    /**
     * @requires PHPUnit >= 9
     */
    public function testCreateCommandWithRetry()
    {
        $client = $this->setupClient(MockClient::requests(
            new MockJsonTooManyRetriesResponse(0.001),
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED)));

        $b = $client->createCommand(Sample::createCommand());
        $this->assertResponseIsSuccessful($b);
        $this->assertResponseStatusCodeSame($b, Response::HTTP_CREATED);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandInvalidDescription()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $response = $client->createCommand(ChatInputCommand::createChatCommand('invalid', 'I am valid input that will be treated as being over 100 characters'));
        $this->assertResponseStatusCodeSame($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandInvalidDescriptionValidatorError()
    {
        $description = $this->faker->paragraphsMinimumChars(1000);

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $this->expectException(ValidatorException::class);
        $client->createCommand(ChatInputCommand::createChatCommand('invalid', $description));
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandRateLimited()
    {
        $client = $this->setupClient(MockClient::rateLimit(0.001));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 429 returned for');

        $client->createCommand(Sample::createCommand());
    }

    /**
     * @dataProvider provideInvalidNotEmptyGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandBadGuildArgument($guild)
    {
        $this->expectExceptionMessage('The "guildId" argument must be a string, must implement GuildIdInterface/IdInterface, or be null.');
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createCommand(Sample::createCommand(), $guild);
    }
}