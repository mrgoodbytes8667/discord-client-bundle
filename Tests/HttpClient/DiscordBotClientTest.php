<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordBotClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
class DiscordBotClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait;

    /**
     * @requires PHPUnit >= 9
     */
    public function testCreateCommand()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED),
            MockJsonResponse::makeFixture('HttpClient/edit-command-success.json'),
            MockJsonResponse::makeFixture('HttpClient/add-command-success.json', Response::HTTP_CREATED),
            MockJsonResponse::makeFixture('HttpClient/edit-command-success.json'),
        ]));

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

    public function testCreateCommandInvalidDescription()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $response = $client->createCommand(ApplicationCommand::create('invalid', 'I am valid input that will be treated as being over 100 characters'));
        $this->assertResponseStatusCodeSame($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     *
     */
    public function testGetCommands()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-commands-success.json'),
        ]));

        $commands = $client->getCommands();

        $this->assertIsArray($commands);
        $this->assertCount(1, $commands);

        $this->assertInstanceOf(ApplicationCommand::class, $commands[0]);
        $this->assertEquals('sample', $commands[0]->getName());
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandsFailure(ApplicationCommand $cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommands($guild);
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommand(ApplicationCommand $cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-command-success.json'),
        ]));

        $command = $client->getCommand($cmd, $guild);

        $this->assertInstanceOf(ApplicationCommand::class, $command);
        $this->assertEquals('sample', $command->getName());

        $this->assertEquals($cmd->getId(), $command->getId());
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandFailure(ApplicationCommand $cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommand($cmd, $guild);
    }

    /**
     * @return Generator
     */
    public function provideCommandAndGuild()
    {
        $cmd = new ApplicationCommand();
        $cmd->setId('846542216677566910');

        $guild = new PartialGuild();
        $guild->setId('737645596567095093');

        yield ['command' => $cmd, 'guild' => $guild];
        yield ['command' => $cmd, 'guild' => null];
    }

    /**
     * @return Generator
     */
    public function provideCommandAndGuildClientExceptionResponses()
    {
        $cmd = new ApplicationCommand();
        $cmd->setId('846542216677566910');

        $guild = new PartialGuild();
        $guild->setId('737645596567095093');

        foreach (range(400, 422) as $code) {

            yield ['command' => $cmd, 'guild' => $guild, 'code' => $code];
            yield ['command' => $cmd, 'guild' => null, 'code' => $code];
        }
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommand(ApplicationCommand $cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::make('', Response::HTTP_NO_CONTENT)
        ]));

        $response = $client->deleteCommand($cmd, $guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @param int $code
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandFailure(ApplicationCommand $cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));

        $client->deleteCommand($cmd, $guild);
    }
}
