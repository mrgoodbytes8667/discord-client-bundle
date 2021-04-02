<?php


namespace Bytes\DiscordBundle\Tests;


use Generator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ClientExceptionResponseProviderTrait
 * @package Bytes\DiscordBundle\Tests
 */
trait ClientExceptionResponseProviderTrait
{
    /**
     * @return Generator
     */
    public function provideClientExceptionResponses()
    {
        foreach ($this->provide400Responses() as $code) {
            yield ['code' => $code['code']];
        }
    }

    /**
     * Purposefully not returning HTTP_LOCKED, HTTP_FAILED_DEPENDENCY, and HTTP_TOO_EARLY because they require additional
     * content/headers
     * Not returning HTTP_TOO_MANY_REQUESTS because this framework expects certain headers and this is already tested
     * @return Generator
     */
    public function provide400Responses()
    {
        yield ['code' => Response::HTTP_BAD_REQUEST, 'success' => false];
        yield ['code' => Response::HTTP_UNAUTHORIZED, 'success' => false];
        yield ['code' => Response::HTTP_PAYMENT_REQUIRED, 'success' => false];
        yield ['code' => Response::HTTP_FORBIDDEN, 'success' => false];
        yield ['code' => Response::HTTP_NOT_FOUND, 'success' => false];
        yield ['code' => Response::HTTP_METHOD_NOT_ALLOWED, 'success' => false];
        yield ['code' => Response::HTTP_NOT_ACCEPTABLE, 'success' => false];
        yield ['code' => Response::HTTP_PROXY_AUTHENTICATION_REQUIRED, 'success' => false];
        yield ['code' => Response::HTTP_REQUEST_TIMEOUT, 'success' => false];
        yield ['code' => Response::HTTP_CONFLICT, 'success' => false];
        yield ['code' => Response::HTTP_GONE, 'success' => false];
        yield ['code' => Response::HTTP_LENGTH_REQUIRED, 'success' => false];
        yield ['code' => Response::HTTP_PRECONDITION_FAILED, 'success' => false];
        yield ['code' => Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'success' => false];
        yield ['code' => Response::HTTP_REQUEST_URI_TOO_LONG, 'success' => false];
        yield ['code' => Response::HTTP_UNSUPPORTED_MEDIA_TYPE, 'success' => false];
        yield ['code' => Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, 'success' => false];
        yield ['code' => Response::HTTP_EXPECTATION_FAILED, 'success' => false];
        yield ['code' => Response::HTTP_I_AM_A_TEAPOT, 'success' => false];
        yield ['code' => Response::HTTP_MISDIRECTED_REQUEST, 'success' => false];
        yield ['code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'success' => false];
        yield ['code' => Response::HTTP_UPGRADE_REQUIRED, 'success' => false];
        yield ['code' => Response::HTTP_PRECONDITION_REQUIRED, 'success' => false];
        yield ['code' => Response::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE, 'success' => false];
        yield ['code' => Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS, 'success' => false];
    }

    /**
     * @return Generator
     */
    public function provide100Responses()
    {
        yield ['code' => Response::HTTP_CONTINUE, 'success' => false];
        yield ['code' => Response::HTTP_SWITCHING_PROTOCOLS, 'success' => false];
        yield ['code' => Response::HTTP_PROCESSING, 'success' => false];
        yield ['code' => Response::HTTP_EARLY_HINTS, 'success' => false];
    }

    /**
     * @return Generator
     */
    public function provide200Responses()
    {
        yield ['code' => Response::HTTP_OK, 'success' => true];
        yield ['code' => Response::HTTP_CREATED, 'success' => true];
        yield ['code' => Response::HTTP_ACCEPTED, 'success' => true];
        yield ['code' => Response::HTTP_NON_AUTHORITATIVE_INFORMATION, 'success' => true];
        yield ['code' => Response::HTTP_NO_CONTENT, 'success' => true];
        yield ['code' => Response::HTTP_RESET_CONTENT, 'success' => true];
        yield ['code' => Response::HTTP_PARTIAL_CONTENT, 'success' => true];
        yield ['code' => Response::HTTP_MULTI_STATUS, 'success' => true];
        yield ['code' => Response::HTTP_ALREADY_REPORTED, 'success' => true];
        yield ['code' => Response::HTTP_IM_USED, 'success' => true];
    }

    /**
     * @return Generator
     */
    public function provide300Responses()
    {
        yield ['code' => Response::HTTP_MULTIPLE_CHOICES, 'success' => false];
        yield ['code' => Response::HTTP_MOVED_PERMANENTLY, 'success' => false];
        yield ['code' => Response::HTTP_FOUND, 'success' => false];
        yield ['code' => Response::HTTP_SEE_OTHER, 'success' => false];
        yield ['code' => Response::HTTP_NOT_MODIFIED, 'success' => false];
        yield ['code' => Response::HTTP_USE_PROXY, 'success' => false];
        yield ['code' => Response::HTTP_RESERVED, 'success' => false];
        yield ['code' => Response::HTTP_TEMPORARY_REDIRECT, 'success' => false];
        yield ['code' => Response::HTTP_PERMANENTLY_REDIRECT, 'success' => false];
    }

    /**
     * @return Generator
     */
    public function provide500Responses()
    {
        yield ['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'success' => false];
        yield ['code' => Response::HTTP_NOT_IMPLEMENTED, 'success' => false];
        yield ['code' => Response::HTTP_BAD_GATEWAY, 'success' => false];
        yield ['code' => Response::HTTP_SERVICE_UNAVAILABLE, 'success' => false];
        yield ['code' => Response::HTTP_GATEWAY_TIMEOUT, 'success' => false];
        yield ['code' => Response::HTTP_VERSION_NOT_SUPPORTED, 'success' => false];
        yield ['code' => Response::HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL, 'success' => false];
        yield ['code' => Response::HTTP_INSUFFICIENT_STORAGE, 'success' => false];
        yield ['code' => Response::HTTP_LOOP_DETECTED, 'success' => false];
        yield ['code' => Response::HTTP_NOT_EXTENDED, 'success' => false];
        yield ['code' => Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED, 'success' => false];
    }
}