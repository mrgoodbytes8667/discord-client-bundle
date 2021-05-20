<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\ValidateUserTrait;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\User;
use Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetUserTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetUserTest extends TestDiscordBotClientCase
{
    use ValidateUserTrait;

    /**
     * @dataProvider provideValidUsers
     *
     * @param string $file
     * @param $userId
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetUser(string $file, $userId)
    {
        $user = $this
            ->setupResponse($file, type: User::class)
            ->deserialize();

        $this->validateUser($user, '272930239796055326', 'elvie70', 'cba426068ee1c51edab2f0c38549f4bc', '6793', 0, true);
    }

    /**
     * @return Generator
     */
    public function provideValidUsers()
    {
        $user = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $user->method('getId')
            ->willReturn('230858112993375816');

        yield ['file' => 'HttpClient/get-user.json', 'userId' => '230858112993375816'];
        yield ['file' => 'HttpClient/get-user.json', 'userId' => $user];

        yield ['file' => 'HttpClient/get-me.json', 'userId' => '@me'];
    }
}
