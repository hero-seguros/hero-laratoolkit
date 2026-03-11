<?php

namespace HeroLaraToolkit\HealthCheck\Http\Controllers;

use HeroLaraToolkit\HealthCheck\Checks\DatabaseConnectionCheck;
use HeroLaraToolkit\HealthCheck\Checks\RedisConnectionCheck;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DatabaseHealthController
{
    public function checkInstanceConnection(
        string $connection,
        DatabaseConnectionCheck $databaseConnectionCheck,
        RedisConnectionCheck $redisConnectionCheck
    ): JsonResponse {
        $redisConnections = [];

        foreach (config('healthcheckhero.health.databases.redisNames') as $key => $value) {
            $redisConnections[] = $value;
        }

        $result = null;
        if (in_array(strtolower($connection), $redisConnections)) {
            $result = $redisConnectionCheck->setConnection($connection)->run();
        } else {
            $result = $databaseConnectionCheck->setConnection($connection)->run();
        }

        if (isset($result->status) && $result->status->value == 'ok') {
            return response()->json([
                'connection' => $connection,
                'status' => $result->status->value,
                'summary' => $result->shortSummary,
                'meta' => $result->meta,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'connection' => $connection,
            'status' => $result->status->value,
            'summary' => $result->shortSummary,
            'meta' => $result->meta,
        ], Response::HTTP_BAD_REQUEST);
    }

    public function checkAllConnections(
        DatabaseConnectionCheck $databaseConnectionCheck,
        RedisConnectionCheck $redisConnectionCheck
    ): JsonResponse {
        $mysqlConnections = config('healthcheckhero.health.databases.names');
        $redisConnections = config('healthcheckhero.health.databases.redisNames');

        $results = [];
        foreach ($mysqlConnections as $connection) {
            $results[] = $databaseConnectionCheck->setConnection($connection)->run();
        }

        foreach ($redisConnections as $connection) {
            $results[] = $redisConnectionCheck->setConnection($connection)->run();
        }

        return response()->json($results, Response::HTTP_OK);
    }
}
