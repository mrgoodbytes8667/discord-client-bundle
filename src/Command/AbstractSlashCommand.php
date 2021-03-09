<?php


namespace Bytes\DiscordBundle\Command;


use Bytes\CommandBundle\Command\BaseCommand;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
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
 * Class AbstractSlashCommand
 * @package Bytes\DiscordBundle\Command
 */
abstract class AbstractSlashCommand extends BaseCommand
{

    /**
     * @var DiscordBotClient
     */
    protected $client;

    /**
     * @var PartialGuild[]
     */
    protected $guilds;

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
            $this->guilds = $this->client->getGuilds();
        }
        return $this->guilds;
    }

    /**
     * AbstractSlashCommand constructor.
     * @param DiscordBotClient $client
     */
    public function __construct(DiscordBotClient $client)
    {
        parent::__construct(null);

        $this->client = $client;
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
        parent::interact($input,$output);
        if($input->getOption('global') && $input->hasArgument('guild')) {
            $input->setArgument('guild', null);
        }
    }

    /**
     * Ask which guild should be affected.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper|null $helper
     *
     * @return PartialGuild|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function interactForGuildArgument(InputInterface $input, OutputInterface $output, ?QuestionHelper $helper = null): ?PartialGuild
    {
        if (!$input->getOption('global') && !$input->getArgument('guild')) {
            if(is_null($helper)) {
                $helper = $this->getHelper('question');
            }
            $empty = new PartialGuild();
            $empty->setName('None');
            $empty->setId('-1');
            $question = new ChoiceQuestion(
                'Pick a guild',
                // choices can also be PHP objects that implement __toString() method
                array_merge([$empty], $this->getGuilds()),
                0
            );

            $answer = $helper->ask($input, $output, $question);
            if($answer->getId() == '-1') {
                $answer = null;
            }
        } else {
            $answer = $input->getArgument('guild') ?: null;
        }

        $input->setArgument('guild', $answer);

        return $answer;
    }


    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @see InputInterface::validate()
     * @see InputInterface::bind()
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if(!$this->input->hasArgument('guild')) {
            throw new LogicException('The guild argument must be added for commands inheriting from AbstractSlashCommand.');
        }

        if(!$this->input->hasOption('global')) {
            throw new LogicException('The global option must be added for commands inheriting from AbstractSlashCommand.');
        }
    }
}