<?php

namespace Bytes\DiscordClientBundle\Command;

use Bytes\CommandBundle\Command\CommandStyle;
use Bytes\CommandBundle\Exception\CommandRuntimeException;
use Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordClientBundle\Services\Traits\AddPermissionTrait;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Role;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 *
 */
class SlashPermissionsCommand extends AbstractSlashCommand
{
    use AddPermissionTrait;

    /**
     * @var string
     */
    protected static $defaultName = 'bytes_discord_client:slash:permissions';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Update ';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('guild', InputArgument::OPTIONAL, 'Guild Name')
            ->addArgument('cmd', InputArgument::OPTIONAL, 'Command name')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Role(s)');
    }

    /**
     * @return int
     * @throws NoTokenException
     */
    protected function executeCommand(): int
    {
        /** @var ApplicationCommand $command */
        $command = $this->input->getArgument('cmd');
        /** @var PartialGuild $guild */
        $guild = $this->input->getArgument('guild');
        /** @var Role[] $roles */
        $roles = $this->input->getArgument('roles');

        return self::SUCCESS;
    }

    /**
     * @param DiscordBotClient $client
     * @param SlashCommandsHandlerCollection $commandsCollection
     */
    public function __construct(DiscordBotClient $client, private SlashCommandsHandlerCollection $commandsCollection)
    {
        parent::__construct($client);
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

        $guild = $this->interactForGuildArgument($input, $output, questionText: 'Pick a server to pull commands from');

        $command = $this->interactForExistingCommandsArgument($guild, $input, $output);

        if(empty($command))
        {
            throw new CommandRuntimeException('A command is required to apply permissions to.', displayMessage: true);
        }

        if(($guild?->getId() ?? '-1') === '-1')
        {
            $guild = $this->interactForGuildArgument($input, $output, questionText: 'Pick a server to apply command permissions to', includePlaceholderGuild: false);
        }

        if(empty($guild))
        {
            throw new CommandRuntimeException('A server is required to apply permissions to.', displayMessage: true);
        }

        if (!$input->getArgument('roles')) {

            $roles = $this->client->getGuildRoles($guild)->deserialize();
            $commands = $this->commandsCollection->getCommands();

            $cmd = Arr::first($commands, function ($value) use ($command) {
                /** @var ApplicationCommand $value */
                return $value->getName() === $command->getName();
            });

            if (empty($roles)) {
                throw new Exception("There are no roles for " . $guild->getName());
            }
            $question = new ChoiceQuestion(
                "Pick a role <info>(ie: 1)</info> or roles <info>(ie: 1, 3)</info> to apply command permissions to",
                // choices can also be PHP objects that implement __toString() method
                $roles,
            );
            $question->setMultiselect(true);

            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('roles', $answer);
        }
    }
}