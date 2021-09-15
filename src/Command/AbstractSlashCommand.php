<?php


namespace Bytes\DiscordClientBundle\Command;


use Bytes\CommandBundle\Command\BaseCommand;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 *
 */
abstract class AbstractSlashCommand extends BaseCommand
{
    /**
     * @var PartialGuild[]
     */
    protected $guilds;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @return PartialGuild[]|null
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function getGuilds()
    {
        if (empty($this->guilds)) {
            $this->guilds = $this->client->getGuilds()->deserialize();
        }
        return $this->guilds;
    }

    /**
     * @param DiscordBotClient $client
     */
    public function __construct(protected DiscordBotClient $client)
    {
        parent::__construct(null);
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->addOption('global', 'g', InputOption::VALUE_NONE, 'Guild will be skipped and assumed to be global immediately');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        if ($input->getOption('global') && $input->hasArgument('guild')) {
            $input->setArgument('guild', null);
        }
    }

    /**
     * Ask which guild should be affected.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper|null $helper
     * @param string $questionText
     * @param bool $includePlaceholderGuild
     *
     * @return PartialGuild|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function interactForGuildArgument(InputInterface $input, OutputInterface $output, ?QuestionHelper $helper = null, string $questionText = 'Pick a server', bool $includePlaceholderGuild = true): ?PartialGuild
    {
        if (!$input->getOption('global') && !$input->getArgument('guild')) {
            if (is_null($helper)) {
                $helper = $this->getHelper('question');
            }
            $guilds = [];
            if($includePlaceholderGuild) {
                $empty = new PartialGuild();
                $empty->setName('None');
                $empty->setId('-1');
                $guilds = [$empty];
            }
            $retrievedGuilds = $this->getGuilds();
            if (!empty($retrievedGuilds)) {
                $guilds = array_merge($guilds, $retrievedGuilds);
            }
            $question = new ChoiceQuestion(
                $questionText,
                // choices can also be PHP objects that implement __toString() method
                $guilds,
                0
            );

            $answer = $helper->ask($input, $output, $question);
            if ($answer->getId() == '-1') {
                $answer = null;
            }
        } else {
            $answer = $input->getArgument('guild') ?: null;
        }

        $input->setArgument('guild', $answer);

        return $answer;
    }

    /**
     * Ask which command to operate on
     * @param PartialGuild|null $guild
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper|null $helper
     * @param string $questionText
     *
     * @return ApplicationCommand|null
     *
     * @throws ClientExceptionInterface
     * @throws NoTokenException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function interactForExistingCommandsArgument(?PartialGuild $guild, InputInterface $input, OutputInterface $output, ?QuestionHelper $helper = null, string $questionText = 'Pick a command'): ?ApplicationCommand
    {
        if (!$input->getArgument('cmd')) {
            if (is_null($helper)) {
                $helper = $this->getHelper('question');
            }
            $commands = $this->client->getCommands($guild)->deserialize();

            if (empty($commands)) {
                $this->io->warning("There are no " . (!is_null($guild) ? "" : "global ") . "commands" . (is_null($guild) ? "" : " for " . $guild->getName()));
                return $input->getArgument('cmd') ?: null;
            }
            $question = new ChoiceQuestion(
                $questionText,
                // choices can also be PHP objects that implement __toString() method
                $commands,
            );

            $answer = $helper->ask($input, $output, $question);
        } else {
            $answer = $input->getArgument('cmd') ?: null;
        }

        $input->setArgument('cmd', $answer);

        return $answer;
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @see InputInterface::bind()
     *
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasArgument('guild')) {
            throw new LogicException('The guild argument must be added for commands inheriting from AbstractSlashCommand.');
        }

        if (!$input->hasOption('global')) {
            throw new LogicException('The global option must be added for commands inheriting from AbstractSlashCommand.');
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return bool True if flush was called
     */
    protected function flush(): bool
    {
        if(!is_null($this->entityManager))
        {
            $this->entityManager->flush();
            return true;
        }
        return false;
    }
}