<?php


namespace Bytes\DiscordBundle\Handler;

use Bytes\DiscordBundle\Slash\SlashCommandInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use InvalidArgumentException;

/**
 * Class SlashCommandsHandlerCollection
 * @package Bytes\DiscordBundle\Handler
 */
class SlashCommandsHandlerCollection
{
    /**
     * @var SlashCommandInterface[]
     */
    private $list;

    /**
     * @var ApplicationCommand[]
     */
    private $commands;

    /**
     * SlashCommandsHandlerCollection constructor.
     * @param array $commands
     */
    public function __construct($commands)
    {
        $this->list = $commands;
    }

    /**
     * @param string $commandClass
     * @param $key
     */
    private static function hydrateCommand(&$commandClass, $key)
    {
        $commandClass = $commandClass::createCommand();
    }

    /**
     * @return string[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param string[] $list
     * @return $this
     */
    public function setList(array $list): self
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @param string $class
     * @return string
     */
    public function getCommandClass(string $class)
    {
        if (array_key_exists($class, $this->list)) {
            return $this->list[$class];
        }
        $return = array_search($class, $this->list);
        if ($return !== false) {
            return $this->list[$return];
        }
        throw new InvalidArgumentException(sprintf("The supplied key '%s' is not present.", $class));
    }

    /**
     * @return ApplicationCommand[]
     */
    public function getCommands(): array
    {
        if (!empty($this->list) && empty($this->commands)) {
            $this->setCommands();
        }
        return $this->commands;
    }

    /**
     * @param string[] $commands
     * @return $this
     */
    private function setCommands(array $commands = []): self
    {
        if (empty($commands) && !empty($this->list)) {
            $commands = $this->list;
        }
        array_walk($commands, array('self', 'hydrateCommand'));
        $this->commands = $commands;
        return $this;
    }

    /**
     * @param string $key
     * @return ApplicationCommand|null
     */
    public function getCommand(string $key)
    {
        if (array_key_exists($key, $this->list)) {
            return $this->commands[$key];
        }
        return null;
    }
}