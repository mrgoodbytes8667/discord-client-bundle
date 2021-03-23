<?php

namespace Bytes\DiscordBundle\Tests\Request;

use Bytes\DiscordBundle\Request\DiscordConverter;
use Bytes\DiscordResponseBundle\Objects\ChannelMention;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\NameInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DiscordConverterTest
 * @package Bytes\DiscordBundle\Tests\Request
 */
class DiscordConverterTest extends TestParamConverterCase
{
    /**
     * @var DiscordConverter
     */
    private $converter;

    /**
     * @dataProvider provideApplyConfigurations
     * @param $class
     * @param string $className
     * @param $name
     * @param $value
     * @param string $responseClass
     * @param string $responseMethod
     */
    public function testApply($class, string $className, $name, $value, string $responseClass, string $responseMethod)
    {
        $request = new Request([], [], [$name => $value]);
        $config = $this->createConfiguration($responseClass, $name);

        $this->assertTrue($this->converter->apply($request, $config));

        $object = $request->attributes->get($name);

        $this->assertInstanceOf($className, $object);
        $response = $object->$responseMethod();
        $this->assertEquals($value, $response);
    }

    /**
     * This shouldn't be possible based on supports but it should return false if it does occur
     */
    public function testApplyBadParamName()
    {
        $this->setupFaker();
        $request = new Request([], [], [$this->faker->camelWords() => $this->faker->camelWords()]);
        $config = $this->createConfiguration(Message::class, 'guild_id');

        $this->assertFalse($this->converter->apply($request, $config));
    }

    /**
     * This shouldn't be possible based on supports but it should return false if it does occur
     */
    public function testApplyBadClass()
    {
        $this->setupFaker();
        $request = new Request([], [], ['guild_id' => $this->faker->camelWords()]);
        $config = $this->createConfiguration('DateTime', 'guild_id');

        $this->assertFalse($this->converter->apply($request, $config));
    }

    /**
     *
     */
    public function testApplyOptionalEmptyParam()
    {
        $this->setupFaker();
        $request = new Request([], [], ['guild_id' => false]);
        $config = $this->createConfiguration(Message::class, 'guild_id', true);

        $this->assertTrue($this->converter->apply($request, $config));

        $object = $request->attributes->get('guild_id');

        $this->assertNull($object);
    }

    /**
     * @dataProvider provideValidConfigurations
     * @dataProvider provideInvalidConfigurations
     * @param $class
     * @param $name
     * @param bool $valid
     */
    public function testSupports($class, $name, bool $valid)
    {
        $config = $this->createConfiguration($class, $name);
        $this->assertEquals($valid, $this->converter->supports($config));
    }

    /**
     * @return \Generator
     */
    public function provideValidConfigurations()
    {
        $this->setupFaker();
        yield ['class' => $this->gm(GuildIdInterface::class), 'name' => 'guild_id', 'valid' => true];
        yield ['class' => $this->gm(GuildIdInterface::class), 'name' => 'guildId', 'valid' => true];
        yield ['class' => $this->gm(NameInterface::class), 'name' => 'name', 'valid' => true];
        yield ['class' => $this->gm(IdInterface::class), 'name' => null, 'valid' => true];
        yield ['class' => $this->gm(IdInterface::class), 'name' => $this->faker->camelWords(), 'valid' => true];
        yield ['class' => $this->gm(IdInterface::class), 'name' => $this->faker->snakeWords(), 'valid' => true];
        yield ['class' => $this->gm(IdInterface::class), 'name' => 'guildId', 'valid' => true];
        yield ['class' => $this->gm(IdInterface::class), 'name' => 'name', 'valid' => true];
    }

    /**
     * @return \Generator
     */
    public function provideApplyConfigurations()
    {
        $this->setupFaker();
        yield ['class' => $this->gm(GuildIdInterface::class), 'className' => GuildIdInterface::class, 'name' => 'guild_id', 'value' => $this->faker->camelWords(), 'responseClass' => Message::class, 'responseMethod' => 'getGuildId'];
        yield ['class' => $this->gm(GuildIdInterface::class), 'className' => GuildIdInterface::class, 'name' => 'guildId', 'value' => $this->faker->camelWords(), 'responseClass' => Message::class, 'responseMethod' => 'getGuildId'];
        yield ['class' => $this->gm(GuildIdInterface::class), 'className' => GuildIdInterface::class, 'name' => 'guild_id', 'value' => $this->faker->camelWords(), 'responseClass' => ChannelMention::class, 'responseMethod' => 'getGuildId'];
        yield ['class' => $this->gm(GuildIdInterface::class), 'className' => GuildIdInterface::class, 'name' => 'guildId', 'value' => $this->faker->camelWords(), 'responseClass' => ChannelMention::class, 'responseMethod' => 'getGuildId'];
        yield ['class' => $this->gm(NameInterface::class), 'className' => NameInterface::class, 'name' => 'name', 'value' => $this->faker->camelWords(), 'responseClass' => PartialGuild::class, 'responseMethod' => 'getName'];
        yield ['class' => $this->gm(IdInterface::class), 'className' => IdInterface::class, 'name' => $this->faker->camelWords(), 'value' => $this->faker->camelWords(), 'responseClass' => PartialGuild::class, 'responseMethod' => 'getId'];
        yield ['class' => $this->gm(IdInterface::class), 'className' => IdInterface::class, 'name' => $this->faker->camelWords(), 'value' => $this->faker->camelWords(), 'responseClass' => Message::class, 'responseMethod' => 'getId'];
        yield ['class' => $this->gm(IdInterface::class), 'className' => IdInterface::class, 'name' => $this->faker->snakeWords(), 'value' => $this->faker->camelWords(), 'responseClass' => PartialGuild::class, 'responseMethod' => 'getId'];
        yield ['class' => $this->gm(IdInterface::class), 'className' => IdInterface::class, 'name' => 'guildId', 'value' => $this->faker->camelWords(), 'responseClass' => PartialGuild::class, 'responseMethod' => 'getId'];
        // responseMethod is getName despite this being an IdInterface: PartialGuild implements IdInterface and NameInterface, and our rules state that NameInterface will take precedence over IdInterface when the name is 'name'
        yield ['class' => $this->gm(IdInterface::class), 'className' => IdInterface::class, 'name' => 'name', 'value' => $this->faker->camelWords(), 'responseClass' => PartialGuild::class, 'responseMethod' => 'getName'];
    }

    /**
     * @param string $class
     * @return MockObject
     */
    public function gm(string $class)
    {
        return $this->getMockBuilder($class)->getMock();
    }

    /**
     * @return \Generator
     */
    public function provideInvalidConfigurations()
    {
        $this->setupFaker();
        yield ['class' => 'DateTime', 'name' => null, 'valid' => false];
        yield ['class' => __CLASS__, 'name' => null, 'valid' => false];
        yield ['class' => null, 'name' => null, 'valid' => false];

        yield ['class' => $this->gm(GuildIdInterface::class), 'name' => $this->faker->camelWords(), 'valid' => false];
        yield ['class' => $this->gm(NameInterface::class), 'name' => $this->faker->camelWords(), 'valid' => false];
        yield ['class' => $this->gm(GuildIdInterface::class), 'name' => 'name', 'valid' => false];
        yield ['class' => $this->gm(NameInterface::class), 'name' => 'guild_id', 'valid' => false];
        yield ['class' => $this->gm(GuildIdInterface::class), 'name' => null, 'valid' => false];
        yield ['class' => $this->gm(NameInterface::class), 'name' => null, 'valid' => false];
    }

    //region Setup/Teardown
    /**
     *
     */
    protected function setUp(): void
    {
        $this->converter = new DiscordConverter();
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        $this->converter = null;
    }
    //endregion
}
