<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\Tests\Common\Constraint\ResponseContentSame;
use Bytes\Tests\Common\Constraint\ResponseStatusCodeSame;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Test\Constraint as ResponseConstraint;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class TestHttpClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
abstract class TestHttpClientCase extends TestCase
{
    use TestFullSerializerTrait, TestFullValidatorTrait;

    public static function assertResponseIsSuccessful(ResponseInterface $response, string $message = ''): void
    {
        self::assertThat(
            $response->getStatusCode(),
            self::logicalAnd(
                self::greaterThanOrEqual(200),
                self::lessThan(300)
            ),
            $message
        );
    }

    public static function assertResponseStatusCodeSame(ResponseInterface $response, int $expectedCode, string $message = ''): void
    {
        self::assertThatForResponse($response, new ResponseStatusCodeSame($expectedCode), $message);
    }

    public static function assertThatForResponse(ResponseInterface $response, Constraint $constraint, string $message = ''): void
    {
        try {
            self::assertThat($response, $constraint, $message);
        } catch (ExpectationFailedException $exception) {
            $headers = $response->getHeaders(false);
            if (array_key_exists('X-Debug-Exception', $headers) && array_key_exists('X-Debug-Exception-File', $headers)) {
                if (($serverExceptionMessage = $headers['X-Debug-Exception'][0])
                    && ($serverExceptionFile = $headers['X-Debug-Exception-File'][0])) {
                    $serverExceptionFile = explode(':', $serverExceptionFile);
                    $exception->__construct($exception->getMessage(), $exception->getComparisonFailure(), new \ErrorException(rawurldecode($serverExceptionMessage), 0, 1, rawurldecode($serverExceptionFile[0]), $serverExceptionFile[1]), $exception->getPrevious());
                }
            }

            throw $exception;
        }
    }

    public static function assertResponseHasHeader(ResponseInterface $response, string $headerName, string $message = ''): void
    {
        self::assertThatForResponse($response, new ResponseConstraint\ResponseHasHeader($headerName), $message);
    }

    public static function assertResponseHasContent(ResponseInterface $response, string $message = ''): void
    {
        static::assertThat($response->getContent(false), static::logicalNot(static::isEmpty()), $message);
    }

    public static function assertResponseHasNoContent(ResponseInterface $response, string $message = ''): void
    {
        static::assertThat($response->getContent(false), static::logicalAnd(static::isEmpty()), $message);
    }

    public static function assertResponseContentSame(ResponseInterface $response, string $content, string $message = ''): void
    {
        self::assertThatForResponse($response, new ResponseContentSame($content), $message);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return mixed
     */
    abstract protected function setupClient(HttpClientInterface $httpClient);
}