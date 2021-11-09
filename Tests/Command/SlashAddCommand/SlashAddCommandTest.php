<?php

namespace Bytes\DiscordClientBundle\Tests\Command\SlashAddCommand;

use Bytes\DiscordClientBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordClientBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordClientBundle\Tests\Command\TestSlashCommand;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Exception;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandCompletionTester;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Class SlashAddCommandTest
 * @package Bytes\DiscordClientBundle\Tests\Command\SlashAddCommand
 *
 * @todo Add missing tests last present in #fba4167e71255eb871c015754d5fb09969c7ceee
 */
class SlashAddCommandTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord_client:slash:add';

    /**
     * @group success
     * @dataProvider provideArguments
     */
    public function testCommandSuccess($callback, $cmd, $guild, $commandId, $args, $verb)
    {
        $commandTester = $this->setupCommandTester($callback, array('1', '1'));

        $commandTester->execute($args);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Look for keywords in the output since it gets arbitrarily wrapped by GitHub Actions
        $format = implode("%A", ['[OK]', "'" . $cmd . "'", $guild, $verb, 'successfully', $commandId]);
        $this->assertStringMatchesFormat('%A' . $format . '%A', $output);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @return Generator
     */
    public function provideArguments(): Generator
    {
        yield 'add-sample-alpha-interactive' => ['callback' => MockSuccessfulAddCallback::class, 'cmd' => 'sample', 'guild' => 'Sample Server Alpha', 'commandId' => '846542216677566910', 'args' => [], 'verb' => 'created'];
        yield 'add-sample-alpha' => ['callback' => MockSuccessfulAddCallback::class, 'cmd' => 'sample', 'guild' => 'Sample Server Alpha', 'commandId' => '846542216677566910', 'args' => ['cmd' => 'sample', 'guild' => 'Sample Server Alpha'], 'verb' => 'created'];

        yield 'edit-sample-alpha-interactive' => ['callback' => MockSuccessfulAddCallback::class, 'cmd' => 'sample', 'guild' => 'Sample Server Alpha', 'commandId' => '846542216677566910', 'args' => ['--commandId' => '846542216677566910'], 'verb' => 'edited'];
        yield 'edit-sample-alpha' => ['callback' => MockSuccessfulAddCallback::class, 'cmd' => 'sample', 'guild' => 'Sample Server Alpha', 'commandId' => '846542216677566910', 'args' => ['cmd' => 'sample', 'guild' => 'Sample Server Alpha', '--commandId' => '846542216677566910'], 'verb' => 'edited'];
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

        $this->expectException(Exception::class);
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
        yield 'search' => [[''], ['sample', 'bar', 'foo']];
        yield 'search s' => [['s'], ['sample']];
        yield 'guild' => [['sample', ''], ['Sample Server Alpha']];
        yield 'guild Sample Ser' => [['sample', 'Sample Ser'], ['Sample Server Alpha']];
    }
}
