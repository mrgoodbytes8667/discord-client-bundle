<?php

namespace Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand;

use Bytes\DiscordBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordBundle\Tests\Command\TestSlashCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Class SlashDeleteCommandTest
 * @package Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand
 */
class SlashDeleteCommandTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord:slash:delete';

    /**
     * @group success
     */
    public function testSuccessfulDelete()
    {
        $commandTester = $this->setupApplication(MockSuccessfulDeleteCallback::class, array('1', '1'));

        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', "'sample2'", '855523182027779516', 'Sample Server Alpha', 'deleted', 'successfully']);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
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
    public function testGetCommandFailure()
    {
        $commandTester = $this->setupApplication(MockGetCommandFailureCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 401 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testDeleteCommandFailure()
    {
        $commandTester = $this->setupApplication(MockDeleteCommandFailureCallback::class, array('1', '1'));

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group success
     */
    public function testSuccessfulDeleteWithRetries()
    {
        $commandTester = $this->setupApplication(MockSuccessfulDeleteWithRetriesCallback::class, array('1', '1'));

        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', "'sample2'", '855523182027779516', 'Sample Server Alpha', 'deleted', 'successfully']);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
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
}

