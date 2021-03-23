<?php


namespace Bytes\DiscordBundle\Tests\Request;


use Bytes\DiscordBundle\Tests\Fixtures\Providers\SymfonyStringWords;
use Bytes\Tests\Common\TestParamConverterTrait;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class TestParamConverterCase extends TestCase
{
    use TestParamConverterTrait;

    /**
     * @var SymfonyStringWords|Generator
     */
    protected $faker;

    /**
     * @return SymfonyStringWords|Generator
     * @before
     */
    public function setupFaker()
    {
        if(is_null($this->faker))
        {
            $faker = Factory::create();
            $faker->addProvider(new SymfonyStringWords($faker));
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