<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Generator;

/**
 * Trait CommandProviderTrait
 * @package Bytes\DiscordBundle\Tests
 */
trait CommandProviderTrait
{
    use ClientExceptionResponseProviderTrait;

    /**
     * @return Generator
     */
    public function provideCommandAndGuild()
    {
        $ac = new ApplicationCommand();
        $ac->setId('846542216677566910');

        $g = new PartialGuild();
        $g->setId('737645596567095093');

        foreach([$ac, '846542216677566910'] as $cmd)
        {
            foreach([$g, null] as $guild) {
                yield ['command' => $cmd, 'guild' => $guild];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideInvalidCommandAndValidGuild()
    {
        $guild = new PartialGuild();
        $guild->setId('737645596567095093');

        foreach([123, '', null, new \DateTime(), []] as $cmd) {
            yield ['command' => $cmd, 'guild' => $guild];
            yield ['command' => $cmd, 'guild' => null];
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