<?php


namespace Bytes\DiscordClientBundle\Tests\Request;


use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\Tests\Common\TestParamConverterTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class TestParamConverterCase
 * @package Bytes\DiscordClientBundle\Tests\Request
 */
class TestParamConverterCase extends TestCase
{
    use TestParamConverterTrait, TestDiscordFakerTrait;
}