<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\Services\OAuth;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\Fixtures\Providers\AuthorizationCodeGrants;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Illuminate\Support\Arr;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthTest
 * @package Bytes\DiscordBundle\Services
 */
class OAuthTest extends TestCase
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Generator|Internet|Lorem|AuthorizationCodeGrants
     */
    private $faker;

    /**
     * @var array
     */
    private $redirects = [];

    //region setup

    /**
     * @param array $config
     */
    protected function setupUrlGenerator(array $config)
    {
        if(is_null($this->urlGenerator)) {
            $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();


            $this->redirects = [];

            $map = [];
            foreach ($config as $key => $value)
            {
                if(array_key_exists('route_name', $value['redirects']))
                {
                    $url = $this->faker->url();
                    $map[] = [$value['redirects']['route_name'], [], UrlGeneratorInterface::ABSOLUTE_URL, $url];
                    $this->redirects[$key] = $url;
                } else {
                    $this->redirects[$key] = $value['redirects']['url'];
                }
            }

            $urlGenerator->method('generate')
                ->will($this->returnValueMap($map));

            $this->urlGenerator = $urlGenerator;
        }
    }

    /**
     *
     */
    protected function setupFaker()
    {
        if(is_null($this->faker))
        {
            $faker = Factory::create();
            $faker->addProvider(new AuthorizationCodeGrants($faker));
            $this->faker = $faker;
        }
    }

    /**
     * @param array $config
     * @param bool $user
     * @return OAuth
     */
    protected function setupOAuth(array $config, bool $user)
    {
        $this->setupUrlGenerator($config);
        return new OAuth($this->security, $this->urlGenerator, Fixture::CLIENT_ID, $config, $user);
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->method('getUser')
            ->willReturn($user);

        $security = $this->createStub(Security::class);

        $security->method('getToken')
            ->willReturn($token);

        $this->security = $security;

        $this->setupFaker();
    }
    //endregion

    //region teardown
    /**
     *
     */
    protected function tearDown(): void
    {
        $this->security = null;
        $this->urlGenerator = null;
        $this->redirects = [];
        $this->faker = null;
    }
    //endregion

    //region testGetOAuthRedirect
    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetBotOAuthRedirect(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $expected = $this->redirects['bot'];
        $actual = $oauth->getBotOAuthRedirect();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetLoginOAuthRedirect(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $expected = $this->redirects['login'];
        $actual = $oauth->getLoginOAuthRedirect();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetSlashOAuthRedirect(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $expected = $this->redirects['slash'];
        $actual = $oauth->getSlashOAuthRedirect();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetUserOAuthRedirect(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $expected = $this->redirects['user'];
        $actual = $oauth->getUserOAuthRedirect();
        $this->assertEquals($expected, $actual);
    }
    //endregion

    //region testGetScopes
    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetScopesBot(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $this->runTestGetScopes($config['bot']['scopes'], OAuthScopes::getBotScopes(), $oauth->getScopesBot());
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetScopesLogin(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $this->runTestGetScopes($config['login']['scopes'], [
            OAuthScopes::IDENTIFY(),
            OAuthScopes::CONNECTIONS(),
            OAuthScopes::GUILDS(),
        ], $oauth->getScopesLogin());
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetScopesSlash(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $this->runTestGetScopes($config['slash']['scopes'], OAuthScopes::getSlashScopes(), $oauth->getScopesSlash());
    }

    /**
     * @dataProvider provideOAuthConfig
     */
    public function testGetScopesUser(array $config, bool $user)
    {
        $oauth = $this->setupOAuth($config, $user);
        $this->runTestGetScopes($config['user']['scopes'], OAuthScopes::getUserScopes(), $oauth->getScopesUser());
    }
    //endregion

    //region testGetAuthorizationUrl
    /**
     * @dataProvider provideAuthorizationUrl
     */
    public function testGetBotAuthorizationUrl(array $config, bool $user, array $permissions, string $state, ?string $guildId)
    {
        $oauth = $this->setupOAuth($config, $user);
        $url = $oauth->getBotAuthorizationUrl($guildId, $state, $permissions);
        $this->assertStringStartsWith('https://discord.com/api/oauth2/authorize?', $url);
        $this->assertStringContainsString('state=' . urlencode($state), $url);
    }

    /**
     * @dataProvider provideAuthorizationUrl
     */
    public function testGetSlashAuthorizationUrl(array $config, bool $user, array $permissions, string $state, ?string $guildId)
    {
        $oauth = $this->setupOAuth($config, $user);
        $url = $oauth->getSlashAuthorizationUrl($guildId, $state, $permissions);
        $this->assertStringStartsWith('https://discord.com/api/oauth2/authorize?', $url);
        $this->assertStringContainsString('state=' . urlencode($state), $url);
    }

    /**
     * @dataProvider provideAuthorizationUrl
     */
    public function testGetOAuthLoginUrl(array $config, bool $user, array $permissions, string $state, ?string $guildId)
    {
        $oauth = $this->setupOAuth($config, $user);
        $url = $oauth->getOAuthLoginUrl($state, $permissions);
        $this->assertStringStartsWith('https://discord.com/api/oauth2/authorize?', $url);
        $this->assertStringContainsString('state=' . urlencode($state), $url);
    }

    /**
     * @dataProvider provideAuthorizationUrl
     */
    public function testGetUserAuthorizationUrl(array $config, bool $user, array $permissions, string $state, ?string $guildId)
    {
        $oauth = $this->setupOAuth($config, $user);
        $url = $oauth->getUserAuthorizationUrl($state, $permissions);
        $this->assertStringStartsWith('https://discord.com/api/oauth2/authorize?', $url);
        $this->assertStringContainsString('state=' . urlencode($state), $url);
    }

    //endregion

    /**
     * @dataProvider provideAuthorizationCodeGrants
     */
    public function testGetAuthorizationCodeGrantURL(array $config, bool $user, array $permissions, string $redirect, array $scopes, string $state, string $endpoint, string $responseType, ?string $guildId, ?bool $disableGuildSelect, $prompt)
    {
        $oauth = $this->setupOAuth($config, $user);
        $url = $oauth->getAuthorizationCodeGrantURL($permissions, $redirect, $scopes, $state, $endpoint, $responseType, $guildId, $disableGuildSelect, $prompt);

        $this->assertStringStartsWith('https://discord.com/api/oauth2/authorize?', $url);

        $this->assertStringContainsString('redirect_uri=' . urlencode($redirect), $url);
    }

    //region Data Providers
    /**
     * @return \Generator
     */
    public function provideEndpoints()
    {
        $endpoints = ['bot', 'slash', 'user', 'login'];
        foreach ($this->provideRedirects() as $redirect) {
            foreach ($endpoints as $key) {
                $config[$key] = [
                    'permissions' => [
                        'add' => [],
                        'remove' => [],
                    ],
                    'scopes' => [
                        'add' => [],
                        'remove' => [],
                    ]
                ];

                $config[$key]['redirects'] = $redirect['redirects'];
            }
            yield ['config' => $config];

        }
    }

    /**
     * @return \Generator
     */
    public function provideRedirects()
    {
        yield ['redirects' => [
            'method' => 'route_name',
            'route_name' => 'abc123'
        ]];
        yield ['redirects' => [
            'method' => 'url',
            'url' => 'https://www.example.com'
        ]];
    }

    /**
     * @return \Generator
     */
    public function provideOAuthConfig()
    {
        $this->setupFaker();
        foreach(range(1, 10) as $i) {
            yield [
                'config' => [
                    'bot' => [
                        'permissions' => $this->faker->permissionsAddRemove(),
                        'scopes' => $this->faker->scopesAddRemove(),
                        'redirects' => $this->faker->redirects(),
                    ],
                    'slash' => [
                        'permissions' => $this->faker->permissionsAddRemove(),
                        'scopes' => $this->faker->scopesAddRemove(),
                        'redirects' => $this->faker->redirects(),
                    ],
                    'user' => [
                        'permissions' => $this->faker->permissionsAddRemove(),
                        'scopes' => $this->faker->scopesAddRemove(),
                        'redirects' => $this->faker->redirects(),
                    ],
                    'login' => [
                        'permissions' => $this->faker->permissionsAddRemove(),
                        'scopes' => $this->faker->scopesAddRemove(),
                        'redirects' => $this->faker->redirects(),
                    ],
                ],
                'user' => $this->faker->boolean()
            ];
        }
    }

    /**
     * @return \Generator
     */
    public function provideAuthorizationCodeGrants()
    {
        $this->setupFaker();
        foreach($this->provideOAuthConfig() as $i) {
            yield [
                'config' => $i['config'],
                'user' => $i['user'],
                'permissions' => $this->faker->permissions(),
                'redirect' => $this->faker->redirect(),
                'scopes' => $this->faker->scopes(),
                'state' => $this->faker->randomLetter(),
                'endpoint' => $this->faker->endpoint(),
                'responseType' => $this->faker->responseType(),
                'guildId' => $this->faker->boolean() ? $this->faker->guildId() : null,
                'disableGuildSelect' => $this->faker->disableGuildSelect(),
                'prompt' => $this->faker->boolean() ? $this->faker->prompt() : null,
            ];
        }
    }

    /**
     * @return \Generator
     */
    public function provideAuthorizationUrl()
    {
        $this->setupFaker();
        foreach($this->provideOAuthConfig() as $i) {
            yield [
                'config' => $i['config'],
                'user' => $i['user'],
                'permissions' => $this->faker->permissions(),
                'state' => $this->faker->randomLetter(),
                'guildId' => $this->faker->boolean() ? $this->faker->guildId() : null,
            ];
        }
    }
    //endregion

    /**
     * @param array $config
     * @param array $expected
     * @param array $scopes
     */
    protected function runTestGetScopes(array $config, array $expected, array $scopes)
    {
        $add = $config['add'];

        $expected = array_unique(array_merge($expected, $add));

        $expected = Arr::where($expected, function ($value, $key) use ($config) {
            return !in_array($value, $config['remove']);
        });
        $this->assertEquals($expected, $scopes);
    }
}
