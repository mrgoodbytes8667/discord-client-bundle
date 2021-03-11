<?php

namespace Bytes\DiscordBundle\Tests;

use Bytes\DiscordBundle\BytesDiscordBundle;
use Bytes\DiscordBundle\Tests\Command\MockServerExceptionCallback;
use Bytes\DiscordBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordBundle\Tests\Command\MockUnauthorizedCallback;
use Bytes\DiscordBundle\Tests\Command\SlashAddCommand\MockSuccessfulAddCallback;
use Bytes\DiscordBundle\Tests\Command\SlashAddCommand\MockSuccessfulEditCallback;
use Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand\MockDeleteCommandFailureCallback;
use Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand\MockGetCommandFailureCallback;
use Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand\MockSuccessfulDeleteCallback;
use Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand\MockSuccessfulDeleteWithRetriesCallback;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Bar;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Foo;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use Exception;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class TestingKernel
 * @package Bytes\DiscordBundle\Tests
 */
class TestingKernel extends Kernel
{
    /**
     * @var string
     */
    private $callback;

    /**
     * @var array
     */
    private $config;

    /**
     * @var bool
     */
    private $registerSlashCommands = true;


    /**
     * TestingKernel constructor.
     * @param string $callback
     * @param array $config = ['client_id' => '', 'client_secret' => '', 'client_public_key' => '', 'bot_token' => '', 'user_agent' => '']
     *
     * All configured values are randomly generated using the same character sets and lengths as actual Discord values
     */
    public function __construct(string $callback = '', array $config = [])
    {
        $this->callback = $callback;
        $this->config = array_merge([
            'client_id' => '586568566858692924',
            'client_secret' => 'RsgAvyEhVKD9Qrjeg2J9iFw1u5fQswmw',
            'client_public_key' => '3c0550fa220b400914edabf283ac1174bf4f99d55781d1ff8ff0e96b02583aec',
            'bot_token' => '84B.cGe2KLFHp6MWtMetEm5m7-mmDcYKuNr3XeuoKvkwBfPhunm.o.BTtNk',
            'user_agent' => 'Discord Bundle PHPUnit Test (https://www.github.com, 0.0.1)',
        ], $config);

        parent::__construct('test', true);
    }

    /**
     * @param bool $registerSlashCommands
     * @return $this
     */
    public function setRegisterSlashCommands(bool $registerSlashCommands): self
    {
        $this->registerSlashCommands = $registerSlashCommands;
        return $this;
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new BytesDiscordBundle(),
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

            $container->register(MockSuccessfulAddCallback::class);
            $container->register(MockSuccessfulEditCallback::class);
            $container->register(MockServerExceptionCallback::class);

            $container->register(MockSuccessfulDeleteCallback::class);
            $container->register(MockUnauthorizedCallback::class);
            $container->register(MockGetCommandFailureCallback::class);
            $container->register(MockDeleteCommandFailureCallback::class);
            $container->register(MockSuccessfulDeleteWithRetriesCallback::class);
            $container->register(MockTooManyRequestsCallback::class);
            $container->register('http_client', MockHttpClient::class);

            if ($this->registerSlashCommands) {
                $container->register(Sample::class)->addTag('bytes_discord.slashcommand');
                $container->register(Foo::class)->addTag('bytes_discord.slashcommand');
                $container->register(Bar::class)->addTag('bytes_discord.slashcommand');
            }

            $container->loadFromExtension('framework', [
                'http_client' => [
                    'mock_response_factory' => $this->callback,
                ],
            ]);

            $container->loadFromExtension('bytes_discord', $this->config);
        });
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
    public function mergeConfig(array $config): self {
        $this->config = array_merge($this->config, $config);
        return $this;
    }
}
