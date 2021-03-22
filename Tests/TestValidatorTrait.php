<?php


namespace Bytes\DiscordBundle\Tests;


use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Trait TestValidatorTrait
 * @package Bytes\DiscordBundle\Tests
 *
 * @deprecated
 */
trait TestValidatorTrait
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @before
     * @return RecursiveValidator|ValidatorInterface
     */
    protected function createValidator()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return $this->validator;
    }

    /**
     * @after
     */
    protected function tearDownValidator(): void
    {
        $this->validator = null;
    }
}