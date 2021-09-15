<?php

namespace Bytes\DiscordClientBundle\Tests\Command\SlashPermissionsCommand;

use Bytes\DiscordClientBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordClientBundle\Tests\Command\TestSlashCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 *
 */
class SlashPermissionsCommandTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord_client:slash:permissions';

    /**
     * @group success
     */
    public function testSuccessfulInteractive()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulPermissionsCallback::class, array('1', '0', '0', '1, 2'));

        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', 'Permissions', 'updated']);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testServerExceptionFailureInteractive()
    {
        $commandTester = $this->setupCommandTester(MockServerExceptionCallback::class, array('1', '0', '0', '1, 2'));

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
        $commandTester = $this->setupCommandTester(MockUnauthorizedCallback::class, array('1', '0', '0', '1, 2'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 401 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testTooManyRequestsInteractive()
    {
        $commandTester = $this->setupCommandTester(MockTooManyRequestsCallback::class, array('1', '0', '0', '1, 2'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 429 returned for');

        $commandTester->execute([]);
    }
}
