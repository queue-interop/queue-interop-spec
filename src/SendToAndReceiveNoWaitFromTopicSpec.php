<?php

namespace Interop\Queue\Spec;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrTopic;
use PHPUnit\Framework\TestCase;

/**
 * @group functional
 */
abstract class SendToAndReceiveNoWaitFromTopicSpec extends TestCase
{
    /**
     * @var PsrContext
     */
    private $context;

    protected function tearDown()
    {
        if ($this->context) {
            $this->context->close();
        }

        parent::tearDown();
    }

    public function test()
    {
        $this->context = $context = $this->createContext();
        $topic = $this->createTopic($context, 'send_to_and_receive_no_wait_from_topic_spec');

        $consumer = $context->createConsumer($topic);

        // guard
        $this->assertNull($consumer->receiveNoWait());

        $expectedBody = __CLASS__.time();

        $context->createProducer()->send($topic, $context->createMessage($expectedBody));

        $startTime = microtime(true);
        $message = $consumer->receiveNoWait();

        $this->assertLessThan(2, microtime(true) - $startTime);

        $this->assertInstanceOf(PsrMessage::class, $message);
        $consumer->acknowledge($message);

        $this->assertSame($expectedBody, $message->getBody());
    }

    /**
     * @return PsrContext
     */
    abstract protected function createContext();

    /**
     * @param PsrContext $context
     * @param string     $topicName
     *
     * @return PsrTopic
     */
    protected function createTopic(PsrContext $context, $topicName)
    {
        return $context->createTopic($topicName);
    }
}
