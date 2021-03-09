<?php

namespace Bytes\DiscordBundle\Command;

use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SlashDeleteCommand
 * @package Bytes\DiscordBundle\Command
 */
class SlashDeleteCommand extends AbstractSlashCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'bytes_discord:slash:delete';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Remove a slash command from a server or globally';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription(self::$defaultDescription)
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

            if ($response->getStatusCode() < 300) {
                $this->io->success(sprintf("The command '%s' (ID: %s) for %s has been deleted successfully.", $command->getName(), $command->getId(), $guild ?? 'global'));
            } else {
                throw new Exception(sprintf("There was an error deleting command '%s' (ID: %s) for %s", $command->getName(), $command->getId(), $guild ?? 'global'));
            }

            //dump($response->getStatusCode(), $response->getContent());
        } catch (ClientException $exception) {
            $this->io->error($exception->getMessage());
            //dump($exception->getResponse()->getContent(false));
            return self::FAILURE;
        } catch (Exception $exception) {
            $this->io->error($exception->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @todo This logic doesn't actually work if arguments are provided
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $guild = $this->interactForGuildArgument($input, $output);

        if (!$input->getArgument('cmd')) {

            $commands = $this->client->getCommands($guild);

            if (empty($commands)) {
                throw new Exception("There are no " . (!is_null($guild) ? "" : "global ") . "commands" . (is_null($guild) ? "" : " for " . $guild->getName()));
            }
            $question = new ChoiceQuestion(
                'Pick a command',
                // choices can also be PHP objects that implement __toString() method
                $this->client->getCommands($guild),
            );
        }

        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $input->setArgument('cmd', $answer);
    }

}
