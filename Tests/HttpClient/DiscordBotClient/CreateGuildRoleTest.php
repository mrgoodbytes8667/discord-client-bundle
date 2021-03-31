<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateGuildRoleTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class CreateGuildRoleTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait, TestDiscordFakerTrait;

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
     * @return Generator
     */
    public function provideValidCreateGuildRole()
    {
        $this->setupFaker();

        foreach ($this->provideValidGuilds() as $guild) {
            foreach ([$this->faker->words(3, true), null] as $name) {
                foreach ([Permissions::SEND_MESSAGES()->value, '2147483648', '2292252672', '0', $this->faker->permissionInteger(), null] as $permission) {
                    foreach ([$this->faker->embedColor(), null] as $color) {
                        foreach ($this->provideBooleansAndNull() as $hoist) {
                            foreach ($this->provideBooleansAndNull() as $mentionable) {
                                yield ['guild' => $guild[0], 'name' => $name, 'permissions' => $permission, 'color' => $color, 'hoist' => $hoist[0], 'mentionable' => $mentionable[0]];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testCreateGuildRoleBadGuildArgument($guild)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->createGuildRole($guild);
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
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
}

