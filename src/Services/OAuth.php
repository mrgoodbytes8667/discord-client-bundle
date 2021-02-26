<?php


namespace Bytes\DiscordBundle\Services;


use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\String\u;

/**
 * Class OAuth
 * @package Bytes\DiscordBundle\Services
 *
 * @method string getUserOAuthRedirect()
 * @method string getBotOAuthRedirect()
 * @method string getLoginOAuthRedirect()
 * @method string getSlashOAuthRedirect()
 */
class OAuth
{
    /**
     * @var string
     */
    private $discordClientId;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $userOAuthRedirect;

    /**
     * @var string
     */
    private $botOAuthRedirect;

    /**
     * @var string
     */
    private $loginOAuthRedirect;

    /**
     * @var string
     */
    private $slashOAuthRedirect;

    /**
     * OAuth constructor.
     * @param string $discordClientId
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $userOAuthRedirect
     * @param string $botOAuthRedirect
     * @param string $loginOAuthRedirect
     * @param string $slashOAuthRedirect
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, string $discordClientId, string $userOAuthRedirect, string $botOAuthRedirect, string $loginOAuthRedirect, string $slashOAuthRedirect)
    {
        $this->urlGenerator = $urlGenerator;
        $this->discordClientId = $discordClientId;
        $this->userOAuthRedirect = $userOAuthRedirect;
        $this->botOAuthRedirect = $botOAuthRedirect;
        $this->loginOAuthRedirect = $loginOAuthRedirect;
        $this->slashOAuthRedirect = $slashOAuthRedirect;
    }

    /**
     * @param array $permissions = Permissions::all()
     * @param string $redirect
     * @param array $scopes = OAuthScopes::all()
     * @param string $state
     * @param string $responseType = ['code']
     * @param string|null $guildId
     * @param bool|null $disableGuildSelect
     * @param null $prompt = [OAuthPrompts::none(),OAuthPrompts::consent()]
     *
     * @return string
     */
    public function getAuthorizationCodeGrantURL(array $permissions, string $redirect, array $scopes, string $state, string $responseType = 'code', ?string $guildId = null, ?bool $disableGuildSelect = null, $prompt = null)
    {
        if (empty($prompt)) {
            $prompt = OAuthPrompts::consent();
        }
        if (is_string($prompt)) {
            $prompt = new OAuthPrompts($prompt);
        }
        $query = [
            'client_id' => $this->discordClientId,
            'permissions' => Permissions::getFlags($permissions),
            'redirect_uri' => $redirect,
            'response_type' => $responseType,
            'scope' => OAuthScopes::buildOAuthString($scopes),
            'state' => $state,
            'prompt' => $prompt->value,
        ];
        if (!empty($guildId)) {
            $query['guild_id'] = $guildId;
            if ($disableGuildSelect === true) {
                $query['disable_guild_select'] = 'true';
            }
        }
        return 'https://discord.com/api/oauth2/authorize?' . http_build_query($query);
    }

    /**
     * @param $name
     * @param $arguments
     * @return string|void
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'getUserOAuthRedirect':
            case 'getBotOAuthRedirect':
            case 'getLoginOAuthRedirect':
            case 'getSlashOAuthRedirect':
                $arg = u($name)->after('get')->snake()->camel()->toString();
                return $this->urlGenerator->generate($this->$arg, [], UrlGeneratorInterface::ABSOLUTE_URL);
                break;
        }
        return;
    }
}