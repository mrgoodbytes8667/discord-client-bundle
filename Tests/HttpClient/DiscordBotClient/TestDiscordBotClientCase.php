<?php


namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;


use Bytes\Common\Faker\Providers\Discord;
use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestDiscordBotClientCase extends \Bytes\DiscordBundle\Tests\HttpClient\TestHttpClientCase
{
    use CommandProviderTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordBotClient
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordBotClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
    }

    /**
     * @return \Generator
     */
    public function provideJsonErrorCodes()
    {
        yield ['jsonCode' => JsonErrorCodes::MISSING_ACCESS(), 'message' => 'Missing Access', 'httpCode' => Response::HTTP_FORBIDDEN];
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_GUILD(), 'message' => 'Unknown Guild', 'httpCode' => Response::HTTP_NOT_FOUND];
        yield ['jsonCode' => JsonErrorCodes::GENERAL_ERROR(), 'message' => '401: Unauthorized', 'httpCode' => Response::HTTP_UNAUTHORIZED];
        yield ['jsonCode' => JsonErrorCodes::UNKNOWN_EMOJI(), 'message' => 'Unknown Emoji', 'httpCode' => Response::HTTP_BAD_REQUEST];
    }

    /**
     * @return \Generator
     */
    public function provideBooleans()
    {
        yield [true];
        yield [false];
    }

    /**
     * @return \Generator
     */
    public function provideBooleansAndNull()
    {
        yield [true];
        yield [false];
        yield [null];
    }

    /**
     * @return Discord|Generator|MiscProvider
     */
    private static function getFaker()
    {
        /** @var Generator|Discord $faker */
        $faker = Factory::create();
        $faker->addProvider(new Discord($faker));

        return $faker;
    }

    /**
     * @return string
     */
    protected static function getRandomEmoji()
    {
        return self::getFaker()->emoji();
    }
}