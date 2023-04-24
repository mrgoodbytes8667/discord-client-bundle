<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateGuildRoleTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
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

        foreach ($this->provideValidGuilds() as $generator) {
            foreach ([$this->faker->words(3, true), null] as $name) {
                foreach ([Permissions::SEND_MESSAGES->value, '2147483648', '2292252672', '0', $this->faker->permissionInteger(), null] as $permission) {
                    foreach ([$this->faker->embedColor(), null] as $color) {
                        foreach ($this->provideBooleansAndNull() as $hoist) {
                            foreach ($this->provideBooleansAndNull() as $mentionable) {
                                yield ['guild' => $generator[0], 'name' => $name, 'permissions' => $permission, 'color' => $color, 'hoist' => $hoist[0], 'mentionable' => $mentionable[0]];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateGuildNameValidationError()
    {
        $this->expectException(ValidatorException::class);

        // This is what it would get if it made it past the validation...
        $client = $this->setupClient(MockClient::requests(MockJsonResponse::make('{"code": 50035, "errors": {"name": {"_errors": [{"code": "BASE_TYPE_MAX_LENGTH", "message": "Must be 100 or fewer in length."}]}}, "message": "Invalid Form Body"}', Response::HTTP_BAD_REQUEST)));
        $client->createGuildRole($this->faker->guildId(),
            'Test Role Number 37 Ad sed blanditiis incidunt quae. Et unde optio corporis. Nihil eum ad odio ab Ad sed blanditiis incidunt quae. Et unde optio corporis. Nihil eum ad odio ab',
            $this->faker->permissionInteger(), $this->faker->embedColor(), $this->faker->boolean(), $this->faker->boolean());
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

