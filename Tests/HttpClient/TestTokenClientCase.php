<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class TestTokenClientCase extends TestHttpClientCase
{

    /**
     * @var HttpClientKernel|null
     */
    protected $kernel;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param string $callback
     * @param array $classes
     * @param array $config = ['client_id' => '', 'client_secret' => '', 'client_public_key' => '', 'bot_token' => '', 'user_agent' => '']
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function setupContainer(string $callback, array $classes = [], array $config = [])
    {
        $kernel = $this->kernel;
        $kernel->setCallback($callback);
        if (!empty($config)) {
            $kernel->mergeConfig($config);
        }
        if(!empty($classes)) {
            $kernel->setClasses($classes);
        }
        $kernel->boot();
        $container = $kernel->getContainer();

        return $container;
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->kernel = new HttpClientKernel();
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_null($this->fs)) {
            $this->fs = new Filesystem();
        }
        $this->fs->remove($this->kernel->getCacheDir());
        $this->kernel = null;
    }

}