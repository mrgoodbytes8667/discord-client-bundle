<?php


namespace Bytes\DiscordBundle\HttpClient;


use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function Symfony\Component\String\u;

/**
 * Class DiscordResponse
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordResponse
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $type;

    /**
     * DiscordResponse constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @param SerializerInterface $serializer
     * @return static
     */
    #[Pure]
    public static function make(SerializerInterface $serializer): static
    {
        return new static($serializer);
    }

    /**
     * @param ResponseInterface $response
     * @param string|null $type
     * @return static
     */
    public function withResponse(ResponseInterface $response, ?string $type): static
    {
        $new = clone $this;
        $new->setResponse($response);
        $new->setType($type);

        return $new;
    }

    //region Getters/Setters

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }
    //endregion

    /**
     * @param bool $throw
     * @param array $context
     * @param string|null $type
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deserialize(bool $throw = true, array $context = [], ?string $type = null)
    {
        if(empty($type ?? $this->type)) {
            throw new \InvalidArgumentException(sprintf('The argument "$type" must be provided to %s if the type property is not set.', __METHOD__));
        }
        try {
            $content = $this->response->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
            if ($throw) {
                throw $exception;
            }
            $content = $exception->getResponse()->getContent(false);
            // If we're deserializing into an array, try to deserialize into a single instance instead and wrap it
            $type = $type ?? $this->type;
            if (u($type)->endsWith('[]')) {
                $single = u($type)->beforeLast('[]')->toString();

                return [$this->serializer->deserialize($content, $single, 'json', $context)];
            }
        }
        return $this->serializer->deserialize($content, $type ?? $this->type, 'json', $context);
    }

    //region Response Helpers

    /**
     * Gets the HTTP status code of the response.
     *
     * @return int|null
     *
     * @throws TransportExceptionInterface when a network error occurs
     */
    public function getStatusCode(): ?int
    {
        return $this->response?->getStatusCode();
    }

    /**
     * Gets the HTTP headers of the response.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @return string[][]|null The headers of the response keyed by header names in lowercase
     *
     * @throws TransportExceptionInterface   When a network error occurs
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     */
    public function getHeaders(bool $throw = true): ?array
    {
        return $this->response?->getHeaders($throw);
    }

    /**
     * Gets the response body as a string.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @return string|null
     *
     * @throws ClientExceptionInterface On a 4xx when $throw is true
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ServerExceptionInterface On a 5xx when $throw is true
     * @throws TransportExceptionInterface When a network error occurs
     */
    public function getContent(bool $throw = true): ?string
    {
        return $this->response?->getContent($throw);
    }
    //endregion
}