<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandOptionType as ACOT;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption as Option;
use Faker\Factory;
use Faker\Generator as FakerGenerator;
use Generator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class BulkOverwriteCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class BulkOverwriteCommandTest extends TestDiscordBotClientCase
{
    use CommandProviderTrait;

    /**
     * @dataProvider provideBulkOverwriteCommand
     * @param $commands
     * @throws TransportExceptionInterface
     */
    public function testBulkOverwriteCommand($commands)
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/bulk-overwrite-global-success.json')));

        $cmd = $client->bulkOverwriteCommands($commands);
        $this->assertResponseIsSuccessful($cmd);
        $this->assertResponseStatusCodeSame($cmd, Response::HTTP_OK);
    }

    /**
     * @dataProvider provideInvalidCommandSetForValidator
     * @param $commands
     */
    public function testValidatorFail($commands)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/add-command-failure-description-too-long.json', Response::HTTP_BAD_REQUEST),
        ]));

        $this->expectException(ValidatorException::class);
        $client->bulkOverwriteCommands($commands);
    }

    /**
     * @return Generator
     */
    public function provideInvalidCommandSetForValidator()
    {
        /** @var FakerGenerator|MiscProvider $faker */
        $faker = Factory::create();
        $faker->addProvider(new MiscProvider($faker));

        $description = $faker->paragraphsMinimumChars(1000);

        yield [
            [
                ApplicationCommand::create('ducimus', $description, [
                    Option::create(ACOT::integer(), 'aut', 'dicta ipsam suscipit'),
                    Option::create(ACOT::role(), 'fugit', 'quisquam quas dolor')
                ])
            ]];
    }
}