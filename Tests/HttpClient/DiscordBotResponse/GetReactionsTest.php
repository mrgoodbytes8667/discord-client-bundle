<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordClientBundle\Tests\HttpClient\ValidateUserTrait;
use Bytes\DiscordResponseBundle\Objects\User;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetReactionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetReactionsTest extends TestDiscordBotClientCase
{
    use ValidateUserTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetReactions()
    {
        /** @var User[] $guilds */
        $users = $this
            ->setupResponse('HttpClient/get-reactions-success.json', type: '\Bytes\DiscordResponseBundle\Objects\User[]')
            ->deserialize();

        $this->assertCount(10, $users);

        $this->validateUser(array_shift($users), "267214096315439358", "kraig31", "a_172328c4e888a2ef87f87f10181cbefd", "7945", 5, true);
        $this->validateUser(array_shift($users), "215879580410922626", "madelynn21", "7a96688978191be1ac01d2d3ceebdd76", "7669", 9, false);
        $this->validateUser(array_shift($users), "283286467684169899", "renner.onie", "3dc833a5309150b290da0ca8f16f6a70", "7299", 3, false);
        $this->validateUser(array_shift($users), "251827868756756876", "amari.morar", null, "9915", 7, true);
        $this->validateUser(array_shift($users), "222748564945326598", "damion.blanda", "a_6df321d96f96b558944f878feb5bbae5", "8463", 1, false);
        $this->validateUser(array_shift($users), "256608243907843676", "meta81", null, "9887", 5, true);
        $this->validateUser(array_shift($users), "254106240423112649", "tcruickshank", "a_ee763912f56a89ec31eb640825b98828", "7461", 0, false);
        $this->validateUser(array_shift($users), "215052384783128171", "kelsie35", "a_5b124e6a26ae9810ee85058735a0c4e3", "0425", 7, true);
        $this->validateUser(array_shift($users), "229766673889780171", "mschamberger", "716344f2ffb3079b0ec491eb99d93d82", "4501", 4, false);
        $this->validateUser(array_shift($users), "234635368258536079", "arjun03", "40aac4371a4d11d391ce187301c27a1d", "7820", 3, false);
    }
}