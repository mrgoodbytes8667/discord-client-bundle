<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\ResponseBundle\HttpClient\Response\Response;

trigger_deprecation('mrgoodbytes8667/discord-bundle', '0.0.1', 'The "%s" class is deprecated, use "%s" in "%s" instead.', 'DiscordResponse', 'Response', 'mrgoodbytes8667/response-bundle');

/**
 * Class DiscordResponse
 * @package Bytes\DiscordBundle\HttpClient
 *
 * @deprecated
 */
class DiscordResponse extends Response
{
}