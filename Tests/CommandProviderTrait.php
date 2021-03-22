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
        $cmd = new ApplicationCommand();
        $cmd->setId('846542216677566910');

        $guild = new PartialGuild();
        $guild->setId('737645596567095093');

        yield ['command' => $cmd, 'guild' => $guild];
        yield ['command' => $cmd, 'guild' => null];
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