<?php

namespace Bytes\DiscordClientBundle\HttpClient\Response;

use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\ResponseBundle\HttpClient\Response\Response;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @method string|null getChannelId()
 * @method DiscordBotClient|null getClient()
 * @method int|null getLimit()
 */
class GetChannelMessagesResponse extends Response
{
    /**
     * @param bool $throw
     * @param array $context
     * @param string|null $type
     * @return Message[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws NoTokenException
     */
    public function deserialize(bool $throw = true, array $context = [], ?string $type = null)
    {
        /** @var Message[] $origResults */
        $origResults = parent::deserialize($throw, $context, $type);
        if (!$this->getFollowPagination() || empty($this->getClient()) || empty($this->getChannelId())) {
            return $origResults;
        }
        $results = $origResults; // To get past the count check for the first iteration
        while (count($origResults) < $this->getLimit() && count($results) > 0) {
            $results = new ArrayCollection($origResults);
            $remainingLimit = $this->getLimit() - count($origResults);
            $results = $this->getClient()
                ->getChannelMessages(channelId: $this->getChannelId(), filter: 'before', messageId: $results->last()->getId(), limit: $remainingLimit, followPagination: false)
                ->deserialize(throw: $throw, context: $context, type: $type);

            $origResults = array_merge($origResults, $results);
        }
        return $origResults;
    }

    /**
     * @return bool
     */
    protected function getFollowPagination()
    {
        if (empty($this->getExtraParams())) {
            return false;
        }
        if (!array_key_exists('followPagination', $this->getExtraParams())) {
            return false;
        }
        return $this->getExtraParams()['followPagination'];
    }
}