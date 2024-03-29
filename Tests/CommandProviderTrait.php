<?php


namespace Bytes\DiscordClientBundle\Tests;


use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\GuildProviderTrait;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandOptionType as ACOT;
use Bytes\DiscordResponseBundle\Objects\Application\Command\ChatInputCommand;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption as Option;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOptionChoice;
use Bytes\Tests\Common\ClientExceptionResponseProviderTrait;
use DateTime;
use Generator;

/**
 * Trait CommandProviderTrait
 * @package Bytes\DiscordClientBundle\Tests
 */
trait CommandProviderTrait
{
    use ClientExceptionResponseProviderTrait, GuildProviderTrait;

    /**
     * @return Generator
     */
    public function provideInvalidCommandAndValidGuild()
    {
        foreach ($this->provideValidGuilds() as $guild) {
            foreach (['', null, new DateTime(), []] as $cmd) {
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

        foreach ($this->provideInvalidNotEmptyGetGuildArguments() as $guild) {
            foreach ($this->provideCommandId() as $cmd) {
                yield ['command' => $cmd['command'], 'guild' => $guild['guild']];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideCommandId()
    {
        $ac = new ChatInputCommand();
        $ac->setId('846542216677566910');

        foreach ([$ac, '846542216677566910'] as $cmd) {
            yield ['command' => $cmd];
        }
    }

    /**
     * @return Generator
     */
    public function provideCommandAndGuildClientExceptionResponses()
    {
        foreach ($this->provideClientExceptionResponses() as $clientExceptionResponse) {
            foreach ($this->provideCommandAndGuild() as $index => $value) {
                yield ['command' => $value['command'], 'guild' => $value['guild'], 'code' => $clientExceptionResponse['code']];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideCommandAndGuild()
    {
        $ac = new ChatInputCommand();
        $ac->setId('846542216677566910');

        $g = new PartialGuild();
        $g->setId('737645596567095093');

        foreach ($this->provideCommandId() as $cmd) {
            foreach ([$g, null] as $guild) {
                yield ['command' => $cmd['command'], 'guild' => $guild];
            }
        }
    }

    /**
     * @return Generator
     */
    public function provideBulkOverwriteCommand()
    {
        yield [
            [
                ChatInputCommand::createChatCommand('ducimus', 'harum et sunt', [
                    Option::create(ACOT::INTEGER, 'aut', 'dicta ipsam suscipit'),
                    Option::create(ACOT::ROLE, 'fugit', 'quisquam quas dolor')
                ]),
                ChatInputCommand::createChatCommand('quae', 'iusto sint quos', [
                    Option::create(ACOT::STRING, 'molestias', 'debitis cum error', false, [
                        ApplicationCommandOptionChoice::create('dolore', 'ut'),
                        ApplicationCommandOptionChoice::create('vel', 'quod'),
                        ApplicationCommandOptionChoice::create('voluptatem', 'fugit')
                    ]),
                    Option::create(ACOT::STRING, 'quas', 'iusto culpa totam', false, [
                        ApplicationCommandOptionChoice::create('voluptates', 'molestiae'),
                        ApplicationCommandOptionChoice::create('quo', 'nisi')
                    ]),
                    Option::create(ACOT::BOOLEAN, 'iure', 'quia dolor ab')
                ]),
                ChatInputCommand::createChatCommand('debitis', 'sapiente error quis', [
                    Option::create(ACOT::BOOLEAN, 'consequuntur', 'omnis dolore debitis'),
                    Option::create(ACOT::BOOLEAN, 'amet', 'cupiditate et beatae'),
                ]),
                ChatInputCommand::createChatCommand('quibusdam', 'vitae totam similique', [
                    Option::create(ACOT::USER, 'illum', 'necessitatibus eligendi nemo', true),
                    Option::create(ACOT::INTEGER, 'aspernatur', 'voluptatem explicabo nisi', false, [
                        ApplicationCommandOptionChoice::create('praesentium', 4),
                        ApplicationCommandOptionChoice::create('voluptate', 9),
                        ApplicationCommandOptionChoice::create('assumenda', 3),
                        ApplicationCommandOptionChoice::create('qui', 6)
                    ]),
                ]),
                ChatInputCommand::createChatCommand('ab', 'odit nisi ex', [
                    Option::create(ACOT::ROLE, 'voluptate', 'perferendis neque maxime', true),
                    Option::create(ACOT::CHANNEL, 'quam', 'eum esse facere'),
                    Option::create(ACOT::INTEGER, 'consectetur', 'assumenda tempore voluptatibus'),
                    Option::create(ACOT::STRING, 'dolorum', 'corrupti eos fuga', false, [
                        ApplicationCommandOptionChoice::create('ut', 'tempore'),
                        ApplicationCommandOptionChoice::create('esse', 'lorem')
                    ]),
                ])
            ]
        ];
    }

}