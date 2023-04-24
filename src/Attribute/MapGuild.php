<?php

namespace Bytes\DiscordClientBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapGuild
{
    public function __construct(public readonly bool $withCounts = false)
    {
    }
}
