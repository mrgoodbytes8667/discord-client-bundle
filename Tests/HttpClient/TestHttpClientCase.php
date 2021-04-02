<?php


namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordResponse;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockStandaloneResponse;
use Bytes\Tests\Common\Constraint\ResponseContentSame;
use Bytes\Tests\Common\Constraint\ResponseStatusCodeSame;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use ErrorException;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Test\Constraint as ResponseConstraint;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class TestHttpClientCase
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
abstract class TestHttpClientCase extends TestCase
{
    use TestFullSerializerTrait, TestFullValidatorTrait;

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param string $message
     * @throws TransportExceptionInterface
     */
    public static function assertResponseIsSuccessful(ResponseInterface|DiscordResponse $response, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        self::assertThat(
            $response->getStatusCode(),
            self::logicalAnd(
                self::greaterThanOrEqual(200),
                self::lessThan(300)
            ),
            $message
        );
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param int $expectedCode
     * @param string $message
     */
    public static function assertResponseStatusCodeSame(ResponseInterface|DiscordResponse $response, int $expectedCode, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        self::assertThatForResponse($response, new ResponseStatusCodeSame($expectedCode), $message);
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param int $expectedCode
     * @param string $message
     */
    public static function assertResponseStatusCodeNotSame(ResponseInterface|DiscordResponse $response, int $expectedCode, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        self::assertThatForResponse($response, static::logicalNot(new ResponseStatusCodeSame($expectedCode)), $message);
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param Constraint $constraint
     * @param string $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function assertThatForResponse(ResponseInterface|DiscordResponse $response, Constraint $constraint, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        try {
            self::assertThat($response, $constraint, $message);
        } catch (ExpectationFailedException $exception) {
            $headers = $response->getHeaders(false);
            if (array_key_exists('X-Debug-Exception', $headers) && array_key_exists('X-Debug-Exception-File', $headers)) {
                if (($serverExceptionMessage = $headers['X-Debug-Exception'][0])
                    && ($serverExceptionFile = $headers['X-Debug-Exception-File'][0])) {
                    $serverExceptionFile = explode(':', $serverExceptionFile);
                    $exception->__construct($exception->getMessage(), $exception->getComparisonFailure(), new ErrorException(rawurldecode($serverExceptionMessage), 0, 1, rawurldecode($serverExceptionFile[0]), $serverExceptionFile[1]), $exception->getPrevious());
                }
            }

            throw $exception;
        }
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param string $headerName
     * @param string $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function assertResponseHasHeader(ResponseInterface|DiscordResponse $response, string $headerName, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        self::assertThatForResponse($response, new ResponseConstraint\ResponseHasHeader($headerName), $message);
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param string $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function assertResponseHasContent(ResponseInterface|DiscordResponse $response, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        static::assertThat($response->getContent(false), static::logicalNot(static::isEmpty()), $message);
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param string $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function assertResponseHasNoContent(ResponseInterface|DiscordResponse $response, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        static::assertThat($response->getContent(false), static::logicalAnd(static::isEmpty()), $message);
    }

    /**
     * @param ResponseInterface|DiscordResponse $response
     * @param string $content
     * @param string $message
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function assertResponseContentSame(ResponseInterface|DiscordResponse $response, string $content, string $message = ''): void
    {
        if ($response instanceof DiscordResponse) {
            $response = $response->getResponse();
        }
        self::assertThatForResponse($response, new ResponseContentSame($content), $message);
    }

    /**
     * @param string|null $fixtureFile
     * @param null $content
     * @param int $code
     * @param string $type
     * @return DiscordResponse
     */
    public function setupResponse(?string $fixtureFile = null, $content = null, int $code = Response::HTTP_OK, $type = stdClass::class): DiscordResponse
    {
        $response = new MockStandaloneResponse(content: $content, fixtureFile: $fixtureFile, statusCode: $code);

        return DiscordResponse::make($this->serializer)->withResponse($response, $type);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return mixed
     */
    abstract protected function setupClient(HttpClientInterface $httpClient);
}