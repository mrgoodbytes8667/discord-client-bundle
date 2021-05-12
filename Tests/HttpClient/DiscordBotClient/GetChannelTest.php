<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ChannelIdInterface;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Generator;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetChannelTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class GetChannelTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait, MessageProviderTrait;

    /**
     * @dataProvider provideValidGetChannelFixtureFiles
     */
    public function testGetChannel(string $file, $channelId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $response = $client->getChannel($channelId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData($file));
    }

    /**
     * @return Generator
     * @todo Remove v6
     */
    public function provideValidGetChannelFixtureFiles()
    {
        foreach ([6, 8] as $apiVersion) {
            $file = sprintf('HttpClient/get-channel-v%d-success.json', $apiVersion);
            $mock = $this
                ->getMockBuilder(ChannelIdInterface::class)
                ->getMock();
            $mock->method('getChannelId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'channelId' => $mock];
            $mock = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $mock->method('getId')
                ->willReturn('230858112993375816');
            yield ['file' => $file, 'channelId' => $mock];
            yield ['file' => $file, 'channelId' => '230858112993375816'];
        }
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testGetChannelFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->getChannel('737645596567095093');
    }

    /**
     * @dataProvider provideInvalidGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetChannelBadChannelArgument($guild)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getChannel($guild);
    }

}

