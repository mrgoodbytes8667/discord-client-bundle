<?php

namespace Bytes\DiscordBundle\Command;

use Bytes\DiscordBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SlashAddCommand
 * @package Bytes\DiscordBundle\Command
 */
class SlashAddCommand extends AbstractSlashCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'bytes_discord:slash:add';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Add a short description for your command';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SlashCommandsHandlerCollection
     */
    private $commandsCollection;

    /**
     * SlashAddCommand constructor.
     * @param DiscordBotClient $client
     * @param SerializerInterface $serializer
     * @param SlashCommandsHandlerCollection $commandsCollection
     */
    public function __construct(DiscordBotClient $client, SerializerInterface $serializer, SlashCommandsHandlerCollection $commandsCollection)
    {
        parent::__construct($client);

        $this->serializer = $serializer;
        $this->commandsCollection = $commandsCollection;
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('cmd', InputArgument::REQUIRED, 'Command name')
            ->addArgument('guild', InputArgument::OPTIONAL, 'Guild Name')
            ->addOption('commandId', 'c', InputOption::VALUE_REQUIRED, 'Optional command Id if updating a command');
    }

    /**
     * @return int
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function executeCommand(): int
    {
        /** @var ApplicationCommand $command */
        $command = $this->input->getArgument('cmd');
        /** @var PartialGuild $guild */
        $guild = $this->input->getArgument('guild');
        if ($guild->getId() === '-1') {
            $guild = null;
        }

        $commandId = $this->input->getOption('commandId');
        if (!empty($commandId)) {
            $command->setId($this->input->getOption('commandId'));
        }

        try {
            $response = $this->client->createCommand($command, $guild);

            if ($response->getStatusCode() < 300) {
                /** @var ApplicationCommand $cmd */
                $cmd = $this->serializer->deserialize($response->getContent(), ApplicationCommand::class, 'json');
                $this->io->success(sprintf("The command '%s' for %s has been %s successfully with ID %s!", $cmd->getName(), $guild ?? 'global', empty($commandId) ? 'created' : 'edited', $cmd->getId()));
            } else {
                throw new Exception(sprintf("There was an error adding command '%s' for %s", $command->getName(), $guild ?? 'global'));
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
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input,$output);
        $questions = [];

        if (!$input->getArgument('cmd')) {
            $question = new ChoiceQuestion(
                'Pick a command',
                // choices can also be PHP objects that implement __toString() method
                array_values($this->commandsCollection->getCommands()),
            );
            $questions['cmd'] = $question;
        }

        $helper = $this->getHelper('question');

        foreach ($questions as $name => $question) {
            $answer = $helper->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }

        $this->interactForGuildArgument($input, $output, true, $helper);
    }

}
