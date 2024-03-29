<?php


namespace Bytes\DiscordClientBundle\HttpClient;


/**
 * Interface DiscordClientEndpoints
 * @package Bytes\DiscordClientBundle\HttpClient
 */
interface DiscordClientEndpoints
{
    //region Scopes
    /**
     * Matches Webhook Send/Edit API routes
     * @var string
     */
    const SCOPE_WEBHOOKS_SEND_EDIT = 'https://discord\.com/api(|/v6|/v8|/v9)/webhooks';

    /**
     * Matches Slash Command API routes
     * @var string
     */
    const SCOPE_SLASH_COMMAND = 'https://discord\.com/api/v9/applications';

    /**
     * Matches OAuth token revoke API routes
     * @var string
     */
    const SCOPE_OAUTH_TOKEN_REVOKE = 'https://discord\.com/api(|/v6|/v8|/v9)/oauth2/token/revoke';

    /**
     * Matches OAuth token API routes
     * @var string
     */
    const SCOPE_OAUTH_TOKEN = 'https://discord\.com/api(|/v6|/v8|/v9)/oauth2/token';

    /**
     * Matches OAuth API routes (though there shouldn't be any...)
     * @var string
     */
    const SCOPE_OAUTH = 'https://discord\.com/api(|/v6|/v8|/v9)/oauth2';

    /**
     * Matches non-oauth API routes
     * @var string
     */
    const SCOPE_API = 'https://discord\.com/api(|/v6|/v8|/v9)/((?!oauth2).)';
    //endregion

    //region Uri
    /**
     * Root Discord API Url
     * @var string
     */
    const ENDPOINT_DISCORD_API = 'https://discord.com/api/';
    
    //endregion
    //region Endpoint Builders
    /**
     * Channels endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_CHANNEL = 'channels';

    /**
     * Guilds endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_GUILD = 'guilds';

    /**
     * Messages endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_MESSAGE = 'messages';

    /**
     * Members endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_MEMBER = 'members';

    /**
     * Users endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_USER = 'users';

    /**
     * Webhooks endpoint (for building purposes)
     * @var string
     */
    const ENDPOINT_WEBHOOK = 'webhooks';

    /**
     * Users @me endpoint (for building purposes)
     * @var string
     */
    const USER_ME = '@me';
    //endregion
}