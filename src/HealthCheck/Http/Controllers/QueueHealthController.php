<?php

namespace HeroLaraToolkit\HealthCheck\Http\Controllers;

use HeroLaraToolkit\HealthCheck\Checks\QueueAwsCheck;
use HeroLaraToolkit\HealthCheck\Checks\QueueCheck;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QueueHealthController
{
    public function checkQueue(
        QueueCheck $queueCheck,
        QueueAwsCheck $queueAwsCheck,
        string $queue
    ): JsonResponse {

        if ($queue === 'sqs') {
            $result = $queueAwsCheck
                ->setWarningThreshold(config('healthcheckhero.health.queue.types.sqs.warningThreshold'))
                ->setFailedThreshold(config('healthcheckhero.health.queue.types.sqs.failedThreshold'))
                ->run();
        } else {
            $queues = $queueCheck
                ->setWarningThreshold(config('healthcheckhero.health.queue.types.database.warningThreshold'))
                ->setFailedThreshold(config('healthcheckhero.health.queue.types.database.failedThreshold'))
                ->getQueues($queue);
            if (empty($queues)) {
                return response()->json([
                    'queue' => $queue,
                    'status' => 'error',
                    'notificationMessage' => "Fila '{$queue}' não encontrada",
                    'meta' => [],
                ], Response::HTTP_BAD_REQUEST);
            }
            $result = $queueCheck->setQueue($queue)->run();
        }

        if (isset($result->status) && $result->status->value == 'ok' || $result->status->value == 'warning') {
            return response()->json([
                'queue' => $queue,
                'status' => $result->status->value,
                'notificationMessage' => $result->notificationMessage,
                'meta' => $result->meta,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'queue' => $queue,
            'status' => $result->status->value,
            'notificationMessage' => $result->notificationMessage,
            'meta' => $result->meta,
        ], Response::HTTP_BAD_REQUEST);
    }

    public function checkAllQueues(QueueCheck $queueCheck, QueueAwsCheck $queueAwsCheck): JsonResponse
    {
        $results = [];
        foreach (config('healthcheckhero.health.queue.types') as $key => $value) {
            if ($key === 'sqs') {
                $results[] = $queueAwsCheck
                    ->setWarningThreshold($value['warningThreshold'])
                    ->setFailedThreshold($value['failedThreshold'])
                    ->run();
            } else {
                $queues = $queueCheck->getQueues();
                foreach ($queues as $queue) {
                    $results[] = $queueCheck
                        ->setWarningThreshold($value['warningThreshold'])
                        ->setFailedThreshold($value['failedThreshold'])
                        ->setQueue($queue)
                        ->run();
                }
            }
        }

        return response()->json($results, Response::HTTP_OK);
    }
}
