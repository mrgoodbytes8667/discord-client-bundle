<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\HttpClient\ValidateUserTrait;
use Bytes\DiscordResponseBundle\Objects\Member;
use Bytes\DiscordResponseBundle\Objects\User;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetGuildMemberTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetGuildMemberTest extends TestDiscordBotClientCase
{
    use ValidateUserTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuildMember()
    {
        /** @var Member $member */
        $member = $this
            ->setupResponse('HttpClient/get-guild-member-success.json', type: Member::class)
            ->deserialize();

        $this->assertInstanceOf(Member::class, $member);
        $this->assertIsArray($member->getRoles());
        $this->assertCount(0, $member->getRoles());
        $this->assertEquals('candice91', $member->getNick());
        $this->assertNotNull($member->getPremiumSince());
        $this->assertNotNull($member->getJoinedAt());
        $this->assertTrue($member->getPending());
        $this->assertTrue($member->getMute());
        $this->assertTrue($member->getDeaf());

        $this->assertInstanceOf(User::class, $member->getUser());
        $this->validateUser($member->getUser(), '253231274338545171', 'beryl.hermann', '95a2fbdbcc4a8f073393ee99fe6205d0', '2803', 3, null);
    }
}