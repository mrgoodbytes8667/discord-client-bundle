<?php

namespace Bytes\DiscordBundle\Tests\Command\SlashAddCommand;

use Bytes\DiscordBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordBundle\Tests\Command\TestSlashCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use function Symfony\Component\String\u;

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
    public function testSuccessfulAddInteractive()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulAddCallback::class, array('1', '1'));

        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', "'sample'", 'Sample Server Alpha', 'created', 'successfully', '846542216677566910']);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group success
     */
    public function testSuccessfulEditInteractive()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulEditCallback::class, array('1', '1'));

        $commandTester->execute([
            '--commandId' => '846542216677566910'
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', "'sample'", 'Sample Server Alpha', 'edited', 'successfully', '846542216677566910']);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testServerExceptionFailureInteractive()
    {
        $commandTester = $this->setupCommandTester(MockServerExceptionCallback::class, array('1', '1'));

        $this->expectException(ServerExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 500 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testUnauthorizedFailureInteractive()
    {
        $commandTester = $this->setupCommandTester(MockUnauthorizedCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 401 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testGetCommandFailureInteractive()
    {
        $commandTester = $this->setupCommandTester('', [], [], false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There are no registered commands.');

        $commandTester->execute([]);
    }

    /**
     * @group failure
     */
    public function testTooManyRequestsInteractive()
    {
        $commandTester = $this->setupCommandTester(MockTooManyRequestsCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 429 returned for');

        $commandTester->execute([]);
    }

    /**
     * @group failure
     */
    public function testInvalidCommandSyntaxInteractive()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group success
     */
    public function testSuccessfulAddCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group success
     */
    public function testSuccessfulEditCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group failure
     */
    public function testServerExceptionFailureCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group failure
     */
    public function testUnauthorizedFailureCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group failure
     */
    public function testGetCommandFailureCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group failure
     */
    public function testTooManyRequestsCli()
    {
        $this->markTestIncomplete('@todo');
    }

    /**
     * @group failure
     */
    public function testInvalidCommandSyntaxCli()
    {
        $this->markTestIncomplete('@todo');
    }
}

