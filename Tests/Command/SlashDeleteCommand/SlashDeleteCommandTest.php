<?php

namespace Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand;

use Bytes\DiscordClientBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordClientBundle\Tests\Command\TestSlashCommand;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandCompletionTester;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Class SlashDeleteCommandTest
 * @package Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand
 *
 * @todo Add missing tests last present in #fba4167e71255eb871c015754d5fb09969c7ceee
 */
class SlashDeleteCommandTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord_client:slash:delete';

    /**
     * @group success
     */
    public function testSuccessfulDeleteInteractive()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulDeleteCallback::class, array('1', '1'));

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
    public function testGetCommandFailureInteractive()
    {
        $commandTester = $this->setupCommandTester(MockGetCommandFailureCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 401 returned for');

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group failure
     */
    public function testDeleteCommandFailureInteractive()
    {
        $commandTester = $this->setupCommandTester(MockDeleteCommandFailureCallback::class, array('1', '1'));

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @group success
     */
    public function testSuccessfulDeleteWithRetriesInteractive()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulDeleteWithRetriesCallback::class, array('1', '1'));

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
    public function testTooManyRequestsInteractive()
    {
        $commandTester = $this->setupCommandTester(MockTooManyRequestsCallback::class, array('1', '1'));

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('HTTP 429 returned for');

        $commandTester->execute([]);
    }

    /**
     * @dataProvider provideCompletionSuggestions
     */
    public function testComplete(array $input, array $expectedSuggestions)
    {
        $command = $this->setupCommand(MockSuccessfulGetGuildsCallback::class);

        $tester = new CommandCompletionTester($command);

        $suggestions = $tester->complete($input);

        foreach ($expectedSuggestions as $expectedSuggestion) {
            $this->assertContains($expectedSuggestion, $suggestions);
        }
    }

    /**
     * @return Generator
     */
    public function provideCompletionSuggestions(): Generator
    {
        yield 'guild' => [[''], ['Sample Server Alpha']];
        yield 'guild Sample Ser' => [['Sample Ser'], ['Sample Server Alpha']];
    }
}

