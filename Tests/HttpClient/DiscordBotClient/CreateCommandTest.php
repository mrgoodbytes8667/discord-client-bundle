<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonTooManyRetriesResponse;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class CreateCommandTest extends TestDiscordBotClientCase
{
    use TestDiscordFakerTrait, GuildProviderTrait;

    /**
     * @requires PHPUnit >= 9
     */
    public function testCreateCommand()
    {
        $client = $this->setupClient(MockClient::requests(
            new MockJsonTooManyRetriesResponse(0.001),
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

        $stub = new PartialGuild();
        $stub->setId('123');

        $b = $client->createCommand(Sample::createCommand(), $stub);
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

        $response = $client->createCommand(ApplicationCommand::create('invalid', 'I am valid input that will be treated as being over 100 characters'));
        $this->assertResponseStatusCodeSame($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandInvalidDescriptionValidatorError()
    {
        $description = '';
        do {
            $description .= $this->faker->paragraph();
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
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createCommand(Sample::createCommand(), $guild);
    }
}

