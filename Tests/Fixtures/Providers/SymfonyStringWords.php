<?php


namespace Bytes\DiscordBundle\Tests\Fixtures\Providers;


use Faker\Provider\Base;
use function Symfony\Component\String\u;

/**
 * Class SymfonyStringWords
 * @package Bytes\DiscordBundle\Tests\Fixtures\Providers
 */
class SymfonyStringWords extends Base
{
    /**
     * @param int $nb
     * @return string
     */
    public function camelWords(int $nb = 3)
    {
        return u($this->generator->words($nb, true))->camel()->toString();
    }

    /**
     * @param int $nb
     * @return string
     */
    public function snakeWords(int $nb = 3)
    {
        return u($this->generator->words($nb, true))->snake()->toString();
    }
}