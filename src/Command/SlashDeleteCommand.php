<?php

namespace Bytes\DiscordClientBundle\Command;

use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


#[AsCommand(name: 'bytes_discord_client:slash:delete', description: 'Remove a slash command from a server or globally')]
class SlashDeleteCommand extends AbstractSlashCommand
{
    /**
     * Adds suggestions to $suggestions for the current completion input (e.g. option or argument).
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('guild')) {
            $guilds = $this->getGuildsInteractive(true);
            $suggestions->suggestValues(array_map(function ($value) {
                return $value->getName();
            }, $guilds));
        }
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->addArgument('guild', InputArgument::OPTIONAL, 'Guild Name')
            ->addArgument('cmd', InputArgument::OPTIONAL, 'Command name');
    }

    /**
     * @return int
     * @throws TransportExceptionInterface
     */
    protected function executeCommand(): int
    {
        /** @var ApplicationCommand $command */
        $command = $this->input->getArgument('cmd');
        /** @var PartialGuild $guild */
        $guild = $this->input->getArgument('guild');


        try {
            $response = $this->client->deleteCommand($command, $guild);
            if ($response->isSuccess()) {
                $response->onSuccessCallback();
                $this->io->success(sprintf("The command '%s' (ID: %s) for %s has been deleted successfully.", $command->getName(), $command->getId(), $guild ?? 'global'));
            } else {
                throw new Exception(sprintf("There was an error deleting command '%s' (ID: %s) for %s", $command->getName(), $command->getId(), $guild ?? 'global'));
            }
        } catch (Exception $exception) {
            $this->io->error($exception->getMessage());
            return self::FAILURE;
        } finally {
            $this->flush();
        }

        return self::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws NoTokenException
     *
     * @todo This logic doesn't actually work if arguments are provided
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $guild = $this->interactForGuildArgument($input, $output);

        if (!$input->getArgument('cmd')) {

            $commands = $this->client->getCommands($guild)->deserialize();

            if (empty($commands)) {
                throw new Exception("There are no " . (!is_null($guild) ? "" : "global ") . "commands" . (is_null($guild) ? "" : " for " . $guild->getName()));
            }
            $question = new ChoiceQuestion(
                'Pick a command',
                // choices can also be PHP objects that implement __toString() method
                $commands,
            );
        }

        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $input->setArgument('cmd', $answer);
    }

}