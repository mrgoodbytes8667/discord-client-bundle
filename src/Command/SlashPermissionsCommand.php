<?php

namespace Bytes\DiscordClientBundle\Command;

use Bytes\CommandBundle\Exception\CommandRuntimeException;
use Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordClientBundle\Services\Traits\AddPermissionTrait;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandPermissionType;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Role;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\GuildApplicationCommandPermission;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'bytes_discord_client:slash:permissions', description: 'Update permissions')]
class SlashPermissionsCommand extends AbstractSlashCommand
{
    use AddPermissionTrait;

    /**
     * @param DiscordBotClient $client
     * @param SlashCommandsHandlerCollection $commandsCollection
     */
    public function __construct(DiscordBotClient $client, private readonly SlashCommandsHandlerCollection $commandsCollection)
    {
        parent::__construct($client);
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this
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

        $permissions = new ArrayCollection();

        foreach ($roles as $role) {
            $permissions = $this->addPermission($permissions, $role->getId());
        }

        try {
            /** @var GuildApplicationCommandPermission|null $deserialize */
            $deserialize = $this->client->editCommandPermissions($guild, $command, $permissions->toArray())->deserialize(false);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface | NoTokenException) {
            $this->io->error('There was an error updating the permissions.');
            return self::FAILURE;
        }

        $this->io->success('Permissions updated.');

        $this->outputPermissionsTable($deserialize, $roles, 'New Permissions');

        return self::SUCCESS;
    }

    /**
     * @param GuildApplicationCommandPermission $existingPermissions
     * @param Role[] $roles
     * @param string $title
     */
    private function outputPermissionsTable(GuildApplicationCommandPermission $existingPermissions, array $roles, string $title)
    {
        if (!empty($existingPermissions) && !empty($existingPermissions->getPermissions())) {
            $table = new Table($this->output);
            $table->setHeaders(['Type', 'Snowflake', 'Permission']);
            $table->setHeaderTitle($title);

            foreach ($existingPermissions->getPermissions() as $applicationCommandPermission) {
                $permissionType = ApplicationCommandPermissionType::tryFrom($applicationCommandPermission->getType());
                $roleOrUserId = $applicationCommandPermission->getId();
                $roleOrUserName = $roleOrUserId;
                if ($permissionType->equals(ApplicationCommandPermissionType::ROLE)) {
                    $foundRole = Arr::first($roles, function ($value) use ($roleOrUserId) {
                        return $value->getId() === $roleOrUserId;
                    });
                    $roleOrUserName = $foundRole?->getName() ?? $roleOrUserId;
                }
                
                $table->addRow([$permissionType->name, $roleOrUserName, $applicationCommandPermission->getPermission()]);
            }
            
            $table->render();
        }
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

        if (empty($command)) {
            throw new CommandRuntimeException('A command is required to apply permissions to.', displayMessage: true);
        }

        if (($guild?->getId() ?? '-1') === '-1') {
            $guild = $this->interactForGuildArgument($input, $output, questionText: 'Pick a server to apply command permissions to', includePlaceholderGuild: false);
        }

        if (empty($guild)) {
            throw new CommandRuntimeException('A server is required to apply permissions to.', displayMessage: true);
        }

        if (!$input->getArgument('roles')) {
            /** @var Role[] $roles */
            $roles = $this->client->getGuildRoles($guild)->deserialize();

            // Get the commands from the handler to check if any permissions should be explicitly excluded
            $commandsList = $this->commandsCollection->getList();
            $commands = $this->commandsCollection->getCommands();

            /** @var ApplicationCommand|null $cmd */
            $cmd = Arr::first($commands, function ($value) use ($command) {
                /** @var ApplicationCommand $value */
                return $value->getName() === $command->getName();
            });

            if (!empty($cmd)) {
                if (!$commandsList[$cmd->getName()]::allowEveryoneRole()) {
                    $roles = Arr::where($roles, function ($value) {
                        return $value->getName() !== '@everyone';
                    });
                }
            }

            try {
                $existingPermissions = $this->client->getCommandPermissions($guild, $command)->deserialize();
            } catch (ClientExceptionInterface) {
                $existingPermissions = new GuildApplicationCommandPermission();
            }
            
            $this->outputPermissionsTable($existingPermissions, $roles, 'Existing Permissions');

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