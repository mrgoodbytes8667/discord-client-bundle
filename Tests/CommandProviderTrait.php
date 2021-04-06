<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\GuildProviderTrait;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Generator;

/**
 * Trait CommandProviderTrait
 * @package Bytes\DiscordBundle\Tests
 */
trait CommandProviderTrait
{
    use ClientExceptionResponseProviderTrait, GuildProviderTrait;

    /**
     * @return Generator
     */
    public function provideCommandAndGuild()
    {
        $ac = new ApplicationCommand();
        $ac->setId('846542216677566910');

        $g = new PartialGuild();
        $g->setId('737645596567095093');

        foreach($this->provideCommandId() as $cmd)
        {
            foreach([$g, null] as $guild) {
                yield ['command' => $cmd['command'], 'guild' => $guild];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideCommandId()
    {
        $ac = new ApplicationCommand();
        $ac->setId('846542216677566910');

        foreach([$ac, '846542216677566910'] as $cmd)
        {
            yield ['command' => $cmd];
        }
    }

    /**
     * @return Generator
     */
    public function provideInvalidCommandAndValidGuild()
    {
        foreach($this->provideValidGuilds() as $guild) {
            foreach ([123, '', null, new \DateTime(), []] as $cmd) {
                yield ['command' => $cmd, 'guild' => $guild[0]];
                yield ['command' => $cmd, 'guild' => null];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideValidCommandAndInvalidNotEmptyGuild()
    {
        $guild = new PartialGuild();
        $guild->setId('737645596567095093');

        foreach($this->provideInvalidNotEmptyGetGuildArguments() as $guild) {
            foreach ($this->provideCommandId() as $cmd) {
                yield ['command' => $cmd['command'], 'guild' => $guild['guild']];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideCommandAndGuildClientExceptionResponses()
    {
        foreach ($this->provideClientExceptionResponses() as $clientExceptionResponse) {
            foreach($this->provideCommandAndGuild() as $index => $value) {
                yield ['command' => $value['command'], 'guild' => $value['guild'], 'code' => $clientExceptionResponse['code']];
            }
        }
    }
}