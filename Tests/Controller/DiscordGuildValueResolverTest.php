<?php

namespace Bytes\DiscordClientBundle\Tests\Controller;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Attribute\MapGuild;
use Bytes\DiscordClientBundle\Controller\ArgumentResolver\DiscordGuildValueResolver;
use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordClientBundle\Tests\TestDiscordGuildTrait;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\Tests\Common\TestArgumentMetadataTrait;
use Illuminate\Support\Arr;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DiscordGuildValueResolverTest extends TestCase
{
    use TestArgumentMetadataTrait, TestDiscordFakerTrait, TestDiscordGuildTrait, DiscordClientSetupTrait {
        DiscordClientSetupTrait::setupBotClient as setupClient;
    }

    /**
     *
     */
    public function testApply()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'guild');

        $object = Arr::first($converter->resolve($request, $config));
        self::assertNotEmpty($object);
        $this->validateClientGetGuildAsGuild($object, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', '282017982734073856', 2, false);
    }

    /**
     *
     */
    public function testApplyOptionWithCounts()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-with-counts-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'guild', false, [new MapGuild(true)]);

        $object = Arr::first($converter->resolve($request, $config));
        self::assertNotEmpty($object);
        $this->validateClientGetGuildAsGuild($object, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', '282017982734073856', 2, true);
    }

    /**
     *
     */
    public function testApplyOptionPartialGuild()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(PartialGuild::class, 'guild', false);

        $object = Arr::first($converter->resolve($request, $config));
        self::assertNotEmpty($object);
        $this->validateClientGetGuildAsPartialGuild($object, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', false);
    }

    /**
     *
     */
    public function testApplyOptionWithCountsPartialGuild()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-with-counts-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(PartialGuild::class, 'guild', false, [new MapGuild(true)]);

        $object = Arr::first($converter->resolve($request, $config));
        self::assertNotEmpty($object);
        $this->validateClientGetGuildAsPartialGuild($object, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', true);
    }

    /**
     *
     */
    public function testSupports()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'fudge');
        self::assertEmpty($converter->resolve(new Request(), $config));
    }

    /**
     *
     */
    public function testSupportsNoClass()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata();
        self::assertEmpty($converter->resolve(new Request(), $config));
    }

    /**
     * This shouldn't be possible based on supports but it should return an empty array if it does occur
     */
    public function testApplyBadParamName()
    {
        $this->setupFaker();

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $request = new Request([], [], [$this->faker->camelWords() => $this->faker->camelWords()]);
        $config = $this->createArgumentMetadata(Guild::class, 'guild_id');

        self::assertEmpty($converter->resolve($request, $config));
    }

    /**
     * This shouldn't be possible based on supports but it should return an empty array if it does occur
     */
    public function testApplyBadClass()
    {
        $this->setupFaker();

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildValueResolver($client);

        $request = new Request([], [], ['guild_id' => $this->faker->camelWords()]);
        $config = $this->createArgumentMetadata('DateTime', 'guild_id');

        self::assertEmpty($converter->resolve($request, $config));
    }

    /**
     *
     */
    public function testApplyApiError()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(MockClient::emptyBadRequest());
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'guild');

        self::expectException(NotFoundHttpException::class);
        $converter->resolve($request, $config);
    }

    /**
     *
     */
    public function testApplyApiErrorUnknownGuild()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(MockClient::jsonErrorCode(JsonErrorCodes::UNKNOWN_GUILD, '', Response::HTTP_NOT_FOUND));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'guild');

        self::expectException(NotFoundHttpException::class);
        $converter->resolve($request, $config);
    }

    /**
     *
     */
    public function testApplyOptionalEmptyParam()
    {
        $request = new Request([], [], ['guild_id' => false]);
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', Response::HTTP_BAD_REQUEST)));
        $converter = new DiscordGuildValueResolver($client);

        $config = $this->createArgumentMetadata(Guild::class, 'guild_id', true);
        self::assertEmpty($converter->resolve($request, $config));
    }
}
