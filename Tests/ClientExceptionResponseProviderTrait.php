<?php


namespace Bytes\DiscordBundle\Tests;


use Generator;

trait ClientExceptionResponseProviderTrait
{
    /**
     * @return Generator
     */
    public function provideClientExceptionResponses()
    {
        foreach (range(400, 422) as $code) {
            yield ['code' => $code];
        }
    }
}