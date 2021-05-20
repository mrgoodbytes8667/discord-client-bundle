<?php

namespace Bytes\DiscordClientBundle\Tests\Request;

use Bytes\DiscordClientBundle\Request\DiscordGuildConverter;
use Bytes\DiscordClientBundle\Services\Client\DiscordBot;
use Bytes\DiscordClientBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordClientBundle\Tests\TestDiscordGuildTrait;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordGuildConverterTest
 * @package Bytes\DiscordClientBundle\Tests\Request
 */
class DiscordGuildConverterTest extends TestParamConverterCase
{
    use TestDiscordGuildTrait, DiscordClientSetupTrait {
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
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild');

        $this->assertTrue($converter->apply($request, $config));

        $object = $request->attributes->get('guild');
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
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild', false, [DiscordGuildConverter::OPTIONS_WITH_COUNTS => true]);

        $this->assertTrue($converter->apply($request, $config));

        $object = $request->attributes->get('guild');
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
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild', false, [DiscordGuildConverter::OPTIONS_CLASS => PartialGuild::class]);

        $this->assertTrue($converter->apply($request, $config));

        $object = $request->attributes->get('guild');
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
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild', false, [DiscordGuildConverter::OPTIONS_CLASS => PartialGuild::class, DiscordGuildConverter::OPTIONS_WITH_COUNTS => true]);

        $this->assertTrue($converter->apply($request, $config));

        $object = $request->attributes->get('guild');
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
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'fudge');
        $this->assertTrue($converter->supports($config));
    }

    /**
     *
     */
    public function testSupportsNoClass()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration();
        $this->assertFalse($converter->supports($config));
    }

    /**
     * This shouldn't be possible based on supports but it should return false if it does occur
     */
    public function testApplyBadParamName()
    {
        $this->setupFaker();

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildConverter($client);

        $request = new Request([], [], [$this->faker->camelWords() => $this->faker->camelWords()]);
        $config = $this->createConfiguration(Guild::class, 'guild_id');

        $this->assertFalse($converter->apply($request, $config));
    }

    /**
     * This shouldn't be possible based on supports but it should return false if it does occur
     */
    public function testApplyBadClass()
    {
        $this->setupFaker();

        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guild-success.json'),
        ]));
        $converter = new DiscordGuildConverter($client);

        $request = new Request([], [], ['guild_id' => $this->faker->camelWords()]);
        $config = $this->createConfiguration('DateTime', 'guild_id');

        $this->assertFalse($converter->apply($request, $config));
    }

    /**
     *
     */
    public function testApplyApiError()
    {
        $request = new Request([], [], ['guild' => 737645596567095093]);
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', Response::HTTP_BAD_REQUEST)));
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild');

        $this->assertFalse($converter->apply($request, $config));
    }

    /**
     *
     */
    public function testApplyOptionalEmptyParam()
    {
        $request = new Request([], [], ['guild_id' => false]);
        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', Response::HTTP_BAD_REQUEST)));
        $converter = new DiscordGuildConverter($client);

        $config = $this->createConfiguration(Guild::class, 'guild_id', true);

        $this->assertTrue($converter->apply($request, $config));

        $object = $request->attributes->get('guild_id');

        $this->assertNull($object);
    }
}
