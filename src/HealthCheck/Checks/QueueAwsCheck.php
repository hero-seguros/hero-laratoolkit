<?php

namespace HeroLaraToolkit\HealthCheck\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Aws\Sqs\SqsClient;

class QueueAwsCheck extends Check
{
    /**
     * @var SqsClient
     */
    protected SqsClient $client;

    /**
     * @var int
     */
    protected int $warningThreshold;

    /**
     * @var int
     */
    protected int $failedThreshold;

    protected string $queueUrl;

    public function __construct()
    {
        $queue = (string) config('healthcheckhero.health.queue.types.sqs.queue', '');
        $prefix = (string) config('healthcheckhero.health.queue.types.sqs.prefix', '');
        $endpoint = (string) config('healthcheckhero.health.queue.types.sqs.endpoint', '');

        if ($prefix !== '') {
            $this->queueUrl = rtrim($prefix, '/') . '/' . ltrim($queue, '/');
        } else {
            $this->queueUrl = rtrim($endpoint, '/') . '/' . ltrim($queue, '/');
        }

        $this->client = new SqsClient([
            'version' => 'latest',
            'endpoint' => config('healthcheckhero.health.queue.types.sqs.endpoint'),
            'region' => config('healthcheckhero.health.queue.types.sqs.region'),
            'queue' => $queue,
            'prefix' => $prefix,
            'credentials' => [
                'key' => config('healthcheckhero.health.queue.types.sqs.key'),
                'secret' => config('healthcheckhero.health.queue.types.sqs.secret')
            ]
        ]);
    }

    public function run(): Result
    {
        try {
            $result = $this->client->getQueueAttributes([
                'QueueUrl' => $this->queueUrl,
                'AttributeNames' => [
                    'ApproximateNumberOfMessages',
                ],
            ]);

            $count = (int) $result['Attributes']['ApproximateNumberOfMessages'];

            if ($count >= $this->failedThreshold) {
                return Result::make()
                    ->failed("SQS com {$count} mensagens")
                    ->meta(['messages' => $count]);
            }

            if ($count >= $this->warningThreshold) {
                return Result::make()
                    ->warning("SQS com {$count} mensagens")
                    ->meta(['messages' => $count]);
            }

            return Result::make()
                ->ok("SQS saudável ({$count} mensagens)")
                ->meta(['messages' => $count]);
        } catch (\Throwable $e) {
            return Result::make()
                ->failed('Erro ao consultar SQS')
                ->meta(['exception' => $e->getMessage()]);
        }
    }

    public function setWarningThreshold(int $warningThreshold): self
    {
        $this->warningThreshold = $warningThreshold;
        return $this;
    }

    public function setFailedThreshold(int $failedThreshold): self
    {
        $this->failedThreshold = $failedThreshold;
        return $this;
    }
}
