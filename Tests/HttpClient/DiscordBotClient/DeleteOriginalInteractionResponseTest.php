<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteOriginalInteractionResponseTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteOriginalInteractionResponseTest extends TestDiscordBotClientCase
{
    use TestDiscordFakerTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testDeleteOriginalInteractionResponse()
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteOriginalInteractionResponse($this->faker->refreshToken());

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
    public function testDeleteOriginalInteractionResponseFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->deleteOriginalInteractionResponse($this->faker->refreshToken());
    }

    /**
     * @todo change to JsonErrorCodes::INVALID_WEBHOOK_TOKEN
     */
    public function testEditFollowupMessageExpiredInteractionToken()
    {
        $client = $this->setupClient(MockClient::jsonErrorCode(50027, 'Invalid Webhook Token', Response::HTTP_UNAUTHORIZED));

        $response = $client->deleteOriginalInteractionResponse($this->faker->refreshToken());

        $this->assertResponseStatusCodeSame($response, Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getJsonErrorCodeData(50027, 'Invalid Webhook Token'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', Response::HTTP_UNAUTHORIZED));

        $response->getContent();
    }
}