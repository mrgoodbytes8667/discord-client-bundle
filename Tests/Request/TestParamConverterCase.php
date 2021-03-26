<?php


namespace Bytes\DiscordBundle\Tests\Request;


use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\Tests\Common\TestParamConverterTrait;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

/**
 * Class TestParamConverterCase
 * @package Bytes\DiscordBundle\Tests\Request
 */
class TestParamConverterCase extends TestCase
{
    use TestParamConverterTrait;

    /**
     * @var MiscProvider|Generator
     */
    protected $faker;

    /**
     * @return MiscProvider|Generator
     * @before
     */
    public function setupFaker()
    {
        if (is_null($this->faker)) {
            $faker = Factory::create();
            $faker->addProvider(new MiscProvider($faker));
            $this->faker = $faker;
        }
        return $this->faker;
    }

    /**
     * @after
     */
    protected function tearDownFaker(): void
    {
        $this->faker = null;
    }
}