<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetChannelsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class GetChannelsTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait;

    /**
     * @dataProvider provideValidGetChannelsFixtureFiles
     */
    public function testGetChannels(string $file, $guildId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getChannels($guildId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    /**
     * @return Generator
     * @todo Remove v6
     */
    public function provideValidGetChannelsFixtureFiles()
    {
        foreach ([6, 8] as $apiVersion) {
            $file = sprintf('HttpClient/get-channels-v%d-success.json', $apiVersion);
            $mock = $this
                ->getMockBuilder(GuildIdInterface::class)
                ->getMock();
            $mock->method('getGuildId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'guildId' => $mock];
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'guildId' => $mock];
            yield ['file' => $file, 'guildId' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelsFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannels('737645596567095093');
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetChannelsBadChannelsArgument($guild)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannels($guild);
    }
}