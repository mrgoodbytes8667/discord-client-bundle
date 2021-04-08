<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Generator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteFollowupMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteFollowupMessageTest extends TestDiscordBotClientCase
{
    use TestDiscordFakerTrait;

    /**
     * @dataProvider provideDeleteFollowupMessage
     * @param $id
     * @param $token
     * @param $messageId
     * @throws TransportExceptionInterface
     */
    public function testDeleteFollowupMessage($token, $messageId)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteFollowupMessage($token, $messageId);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
        $this->assertResponseHasNoContent($response);
        $this->assertResponseContentSame($response, '');
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     */
    public function testDeleteFollowupMessageFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        foreach ($this->provideDeleteFollowupMessage() as $item) {
            $client->deleteFollowupMessage($item['token'], $item['messageId']);
        }
    }

    /**
     * @return Generator
     */
    public function provideDeleteFollowupMessage()
    {
        $this->setupFaker();
        yield ['token' => $this->faker->refreshToken(), 'messageId' => $this->faker->snowflake()];
    }

    /**
     * @todo change to JsonErrorCodes::INVALID_WEBHOOK_TOKEN
     */
    public function testEditFollowupMessageExpiredInteractionToken()
    {
        $client = $this->setupClient(MockClient::jsonErrorCode(50027, 'Invalid Webhook Token', Response::HTTP_UNAUTHORIZED));

        $response = $client->deleteFollowupMessage($this->faker->refreshToken(), $this->faker->snowflake());

        $this->assertResponseStatusCodeSame($response, Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData(50027, 'Invalid Webhook Token'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_UNAUTHORIZED));

        $response->getContent();
    }
}