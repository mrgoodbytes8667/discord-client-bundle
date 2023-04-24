<?php


namespace Bytes\DiscordClientBundle\HttpClient\Retry;


use Bytes\ResponseBundle\HttpClient\Retry\APIRetryStrategy;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DiscordRetryStrategy
 * @package Bytes\DiscordClientBundle\HttpClient\Retry
 */
class DiscordRetryStrategy extends APIRetryStrategy implements RetryStrategyInterface
{
    /**
     *
     * @var string
     */
    const RATELIMITHEADER = 'x-ratelimit-reset-after';

    /**
     * @param AsyncContext $context
     * @param TransportExceptionInterface|null $exception
     * @return int Amount of time to delay in milliseconds
     * @throws Exception
     */
    protected function getRateLimitDelay(AsyncContext $context, ?TransportExceptionInterface $exception): int
    {
        $reset = $this->getReset($context) ?? -1;
        if ($reset > 0) {
            return $reset * 1000;
        } else {
            return $this->calculateDelay($context, $exception);
        }
    }

    /**
     * @param AsyncContext $context
     * @return int
     */
    protected function getReset(AsyncContext $context): int
    {
        if (($context->getInfo('retry_count') ?? 0) > 1) {
            try {
                return self::getHeaderValue($context->getHeaders(), self::RATELIMITHEADER);
            } catch (InvalidArgumentException $exception) {
                return -1;
            }
        }

        return -1;
    }
}