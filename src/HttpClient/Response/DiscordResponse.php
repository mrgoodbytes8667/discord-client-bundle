<?php


namespace Bytes\DiscordClientBundle\HttpClient\Response;


use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Exceptions\MissingAccessException;
use Bytes\DiscordResponseBundle\Exceptions\UnknownObjectException;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ErrorInterface;
use Bytes\ResponseBundle\HttpClient\Response\Response;

/**
 *
 */
class DiscordResponse extends Response
{
    /**
     * @param array $context
     * @param $content
     * @param string|null $type
     *
     * @return array{continue: bool, content: mixed}
     */
    protected function deserializeOnError(array $context, $content, ?string $type): array
    {
        $parent = parent::deserializeOnError($context, $content, $type);
        if (!$parent['continue']) {
            return $parent;
        }

        if (is_subclass_of($type, ErrorInterface::class)) {
            $return = $this->doDeserializeContent($parent['content'], $type, $context);
            $code = JsonErrorCodes::tryFrom($return->getCode());
            if (JsonErrorCodes::isUnknownCodeType($code)) {
                throw new UnknownObjectException($this->getResponse(), $return->getMessage(), $return->getCode());
            } elseif ($code->equals(JsonErrorCodes::MISSING_ACCESS)) {
                throw new MissingAccessException($this->getResponse(), $return->getMessage(), $return->getCode());
            }
        }

        return $parent;
    }

    /**
     * @param bool $throw
     * @param string|null $type
     * @return bool
     */
    protected function deserializeGetThrow(bool $throw, ?string $type): bool
    {
        if ($throw && is_subclass_of($type, ErrorInterface::class)) {
            return false;
        }
        return parent::deserializeGetThrow($throw, $type);
    }
}