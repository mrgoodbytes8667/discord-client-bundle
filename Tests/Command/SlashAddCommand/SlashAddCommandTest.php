<?php

namespace Bytes\DiscordBundle\Tests\Command\SlashAddCommand;

use Bytes\DiscordBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordBundle\Tests\Command\TestSlashCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Class SlashAddCommandTest
 * @package Bytes\DiscordBundle\Tests\Command\SlashAddCommand
 */
class SlashAddCommandTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord:slash:add';

    /**
     * @group success
     */
    public function testSuccessfulAdd()
    {
        $commandTester = $this->setupApplication(MockSuccessfulAddCallback::class, array('1', '1'));

        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("[OK] The command 'sample' for Sample Server Alpha has been created successfully with ID 846542216677566910!", $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group success
     */
    public function testSuccessfulEdit()
    {
        $commandTester = $this->setupApplication(MockSuccessfulEditCallback::class, array('1', '1'));

        $commandTester->execute([
            '--commandId' => '846542216677566910'
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("[OK] The command 'sample' for Sample Server Alpha has been edited successfully with ID 846542216677566910!", $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testServerExceptionFailure()
    {
        $commandTester = $this->setupApplication(MockServerExceptionCallback::class, array('1', '1'));

        $this->expectException(ServerExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 500 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testUnauthorizedFailure()
    {
        $commandTester = $this->setupApplication(MockUnauthorizedCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 401 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testGetCommandFailure()
    {
        $commandTester = $this->setupApplication('', [], [], false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There are no registered commands.');

        $commandTester->execute([]);
    }

    /**
     * @group failure
     */
    public function testTooManyRequests()
    {
        $commandTester = $this->setupApplication(MockTooManyRequestsCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 429 returned for');

        $commandTester->execute([]);
    }

    /**
     * @group failure
     */
    public function testInvalidCommandSyntax()
    {
        $this->markTestIncomplete('@todo');
    }
}

