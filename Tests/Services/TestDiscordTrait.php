<?php


namespace Bytes\DiscordBundle\Tests\Services;


use Bytes\DiscordBundle\Tests\ClientExceptionResponseProviderTrait;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\User;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Trait TestDiscordTrait
 * @package Bytes\DiscordBundle\Tests\Services
 *
 * @method assertInstanceOf(string $expected, $actual, string $message = '')
 * @method assertEquals($expected, $actual, string $message = '')
 * @method assertCount(int $expectedCount, $haystack, string $message = '')
 * @method assertNull($actual, string $message = '')
 * @method expectException(string $exception)
 * @method expectExceptionMessage(string $message)
 * @method setupClient(HttpClientInterface $httpClient)
 * @property SerializerInterface $serializer
 */
trait TestDiscordTrait
{
    use CommandProviderTrait, ClientExceptionResponseProviderTrait;

    /**
     *
     */
    public function testGetGuilds()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-guilds.json'),
        ]));
        $guilds = $client->getGuilds();

        $this->assertCount(2, $guilds);
        $this->assertInstanceOf(PartialGuild::class, $guilds[0]);
        $this->assertInstanceOf(PartialGuild::class, $guilds[1]);
    }

    /**
     * @dataProvider provideClientExceptionResponses
     *
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuildsFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getGuilds();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetMe()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-me.json'),
        ]));

        $user = $client->getMe();
        $this->validateUser($user, '272930239796055326', 'elvie70', 'cba426068ee1c51edab2f0c38549f4bc', '6793', 0, true);
    }

    /**
     * @param $user
     * @param $id
     * @param $username
     * @param $avatar
     * @param $discriminator
     * @param $flags
     * @param $bot
     */
    protected function validateUser($user, $id, $username, $avatar, $discriminator, $flags, $bot)
    {
        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($avatar, $user->getAvatar());
        $this->assertEquals($discriminator, $user->getDiscriminator());
        $this->assertEquals($flags, $user->getPublicFlags());
        $this->assertEquals($bot, $user->getBot());
    }

    /**
     * @dataProvider provideClientExceptionResponses
     * @param int $code
     */
    public function testGetMeFailure(int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getMe();
    }
}