<?php

namespace Bytes\DiscordClientBundle\Tests;

use Bytes\DiscordClientBundle\BytesDiscordClientBundle;
use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\EnumSerializerBundle\BytesEnumSerializerBundle;
use Bytes\ResponseBundle\BytesResponseBundle;
use Exception;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class Kernel
 * @package Bytes\DiscordClientBundle\Tests\Command
 */
class Kernel extends BaseKernel
{
    /**
     * @var string
     */
    protected $callback;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    private $classes = [];

    /**
     * Kernel constructor.
     * @param string $callback
     * @param array $config = ['client_id' => '', 'client_secret' => '', 'client_public_key' => '', 'bot_token' => '', 'user_agent' => '']
     *
     * All configured values are randomly generated using the same character sets and lengths as actual Discord values
     */
    public function __construct(string $callback = '', array $config = [])
    {
        $this->callback = $callback;
        $this->config = array_merge([
            'client_id' => Fixture::CLIENT_ID,
            'client_secret' => Fixture::CLIENT_SECRET,
            'client_public_key' => Fixture::CLIENT_PUBLIC_KEY,
            'bot_token' => Fixture::BOT_TOKEN,
            'user_agent' => Fixture::USER_AGENT,
            'endpoints' => [
                'bot' => [
                    'revoke_on_refresh' => false,
                    'fire_revoke_on_refresh' => true,
                ],
                'user' => [
                    'revoke_on_refresh' => false,
                    'fire_revoke_on_refresh' => true,
                ]
            ]
        ], $config);

        parent::__construct('test', true);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new BytesResponseBundle(),
            new BytesDiscordClientBundle(),
            new BytesEnumSerializerBundle(),
        ];
    }

    /**
     * @param LoaderInterface $loader
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->register('security.helper', Security::class);
            $container->register('router.default', UrlGeneratorInterface::class);
            $container->register('security.csrf.token_manager', CsrfTokenManagerInterface::class);

            foreach ($this->classes as $class) {
                if(is_array($class)) {
                    if(array_key_exists('id', $class) && array_key_exists('class', $class)) {
                        $container->register($class['id'], $class['class']);
                    } else {
                        $container->register($class[0], $class[1]);
                    }
                } else {
                    $container->register($class);
                }
            }

            if ($this->hasCallback() && !in_array($this->callback, $this->classes)) {
                $container->register($this->callback);

                $container->register('http_client', MockHttpClient::class);

                $container->loadFromExtension('framework', [
                    'http_client' => [
                        'mock_response_factory' => $this->callback,
                    ],
                ]);
            }

//            $container->register(MockSuccessfulAddCallback::class);
//            $container->register(MockSuccessfulEditCallback::class);
//            $container->register(MockServerExceptionCallback::class);
//
//            $container->register(MockSuccessfulDeleteCallback::class);
//            $container->register(MockUnauthorizedCallback::class);
//            $container->register(MockGetCommandFailureCallback::class);
//            $container->register(MockDeleteCommandFailureCallback::class);
//            $container->register(MockSuccessfulDeleteWithRetriesCallback::class);
//            $container->register(MockTooManyRequestsCallback::class);


            $container->loadFromExtension('bytes_discord_client', $this->config);
        });
    }

    /**
     * @return bool
     */
    public function hasCallback(): bool
    {
        return !empty($this->callback);
    }

    /**
     * Gets the cache directory.
     *
     * Since Symfony 5.2, the cache directory should be used for caches that are written at runtime.
     * For caches and artifacts that can be warmed at compile-time and deployed as read-only,
     * use the new "build directory" returned by the {@see getBuildDir()} method.
     *
     * @return string The cache directory
     */
    public function getCacheDir()
    {
        return parent::getCacheDir() . '/' . spl_object_hash($this);
    }

    /**
     * @return string
     */
    public function getCallback(): string
    {
        return $this->callback;
    }

    /**
     * @param string $callback
     * @return $this
     */
    public function setCallback(string $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     * @return $this
     */
    public function setClasses(array $classes): self
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * @param string|array $class
     * @return $this
     */
    public function addClass($class): self
    {
        $this->classes[] = $class;
        return $this;
    }
}
