<?php


namespace Bytes\DiscordBundle\Routing;


use BadMethodCallException;
use Bytes\DiscordBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Routing\AbstractOAuth;
use Bytes\ResponseBundle\Routing\OAuthPromptInterface;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

abstract class AbstractDiscordOAuth extends AbstractOAuth
{
    /**
     * @var string
     */
    protected static $promptKey = 'prompt';

    /**
     * @var string
     */
    protected static $baseAuthorizationCodeGrantURL = '';

    /**
     * @return UnicodeString
     */
    protected static function getBaseAuthorizationCodeGrantURL(): UnicodeString
    {
        return u(DiscordClientEndpoints::ENDPOINT_DISCORD_API)->ensureEnd('/')->append('oauth2/authorize')->ensureEnd('?');
    }

    /**
     * @inheritDoc
     */
    protected static function walkHydrateScopes(&$value, $key)
    {
        $value = (new OAuthScopes($value))->value;
    }

    /**
     * Get the external URL begin the OAuth token exchange process
     * @param string|null $state
     * @param ...$options = ['prompt' => new OAuthPrompts()]
     * @return string
     */
    public function getAuthorizationUrl(?string $state = null, ...$options): string
    {
        $options = Push::createPush($options, OAuthPrompts::none(), 'prompt')
            ->value();
        return parent::getAuthorizationUrl($state, ...$options);
    }

    /**
     * Returns the $prompt argument for getAuthorizationCodeGrantURL() after normalization and validation
     * @param OAuthPromptInterface|string|bool|null $prompt
     * @param mixed ...$options
     * @return string|bool
     *
     * @throws BadMethodCallException
     */
    protected function normalizePrompt(bool|OAuthPromptInterface|string|null $prompt, ...$options)
    {
        if ($prompt instanceof OAuthPrompts) {
            return $prompt->prompt();
        } elseif (is_string($prompt) && OAuthPrompts::isValid($prompt)) {
            return OAuthPrompts::make($prompt)->prompt();
        } else {
            return OAuthPrompts::none()->prompt();
        }
    }

    /**
     * Converts the Push object to an array for http_build_query().
     * @param Push $query
     * @return array
     */
    protected function getQueryValues(Push $query): array
    {
        return $query->snake();
    }
}