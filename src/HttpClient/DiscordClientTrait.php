<?php


namespace Bytes\DiscordClientBundle\HttpClient;


use function Symfony\Component\String\u;

/**
 * Trait DiscordClientTrait
 * @package Bytes\DiscordClientBundle\HttpClient
 */
trait DiscordClientTrait
{
    /**
     * @param string $path
     * @param string $version
     * @return string
     */
    protected function buildURL(string $path, string $version = 'v9')
    {
        $url = u($path);
        if ($url->startsWith(DiscordClientEndpoints::ENDPOINT_DISCORD_API)) {
            return $path;
        }
        if (!empty($version)) {
            $url = $url->ensureStart($version . '/');
        }
        return $url->ensureStart(DiscordClientEndpoints::ENDPOINT_DISCORD_API)->toString();
    }
}