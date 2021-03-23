<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\Fixtures\Providers\SymfonyStringWords;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use DateTime;
use Faker\Factory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $faker = Factory::create();
        $faker->addProvider(new SymfonyStringWords($faker));
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
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
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
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
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
        $this->expectException(BadRequestHttpException::class);

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', 400)));

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
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));

        $client->deleteCommand($cmd, $guild);
    }

    /**
     *
     */
    public function testDeleteCommandFailureBadCommandArgument()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', 400)));

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
        $this->expectException(BadRequestHttpException::class);

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', 400)));

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

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));

        $client->getGuild('737645596567095093');
    }
}
