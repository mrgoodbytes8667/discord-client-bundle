<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\Tests\TestSerializerTrait;
use Bytes\DiscordBundle\Tests\TestValidatorTrait;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Test\Constraint as ResponseConstraint;

/**
 * Class TestHttpClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
abstract class TestHttpClientCase extends TestCase
{
    use TestSerializerTrait, TestValidatorTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @return mixed
     */
    abstract protected function setupClient(HttpClientInterface $httpClient);

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
        self::assertThatForResponse($response, new ResponseConstraint\ResponseStatusCodeSame($expectedCode), $message);
    }

    public static function assertResponseHasHeader(ResponseInterface $response, string $headerName, string $message = ''): void
    {
        self::assertThatForResponse($response, new ResponseConstraint\ResponseHasHeader($headerName), $message);
    }

    public static function assertThatForResponse(ResponseInterface $response, Constraint $constraint, string $message = ''): void
    {
        try {
            self::assertThat($response, $constraint, $message);
        } catch (ExpectationFailedException $exception) {
            if (($serverExceptionMessage = $response->headers->get('X-Debug-Exception'))
                && ($serverExceptionFile = $response->headers->get('X-Debug-Exception-File'))) {
                $serverExceptionFile = explode(':', $serverExceptionFile);
                $exception->__construct($exception->getMessage(), $exception->getComparisonFailure(), new \ErrorException(rawurldecode($serverExceptionMessage), 0, 1, rawurldecode($serverExceptionFile[0]), $serverExceptionFile[1]), $exception->getPrevious());
            }

            throw $exception;
        }
    }
}