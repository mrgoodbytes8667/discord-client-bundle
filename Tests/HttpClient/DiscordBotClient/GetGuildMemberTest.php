<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetGuildMemberTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class GetGuildMemberTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait;

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
     * @return Generator
     */
    public function provideValidGetGuildMembers()
    {
        foreach ($this->provideValidGuilds() as $guild) {
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');

            yield ['guild' => $guild[0], 'user' => $mock];
            yield ['guild' => $guild[0], 'user' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideInvalidGetGuildMembers
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetGuildMemberBadGuildArgument($guild, $user)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getGuildMember($guild, $user);
    }

    /**
     * @return Generator
     */
    public function provideInvalidGetGuildMembers()
    {
        foreach ($this->provideInvalidGetGuildArguments() as $guild) {
            foreach ($this->provideInvalidGetGuildArguments() as $user) {
                yield ['guild' => $guild['guild'], 'user' => $user['guild']];
            }
        }
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
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
}

