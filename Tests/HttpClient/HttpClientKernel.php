<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;


use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Kernel;
use Bytes\DiscordBundle\Tests\TestSerializerTrait;
use Bytes\DiscordBundle\Tests\TestValidatorTrait;
use Exception;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HttpClientKernel extends Kernel
{

    /**
     * @param LoaderInterface $loader
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(function (ContainerBuilder $container) {
            $container->register('http_client', MockHttpClient::class)->setPublic(true);
            $container->register('bytes_discord.httpclient.retry_strategy.discord', DiscordRetryStrategy::class)->setPublic(true);
            $container->register('bytes_discord.httpclient.discord.bot', DiscordBotClient::class)->setPublic(true)->setPublic(true);
        });
    }
}