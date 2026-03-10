<?php

namespace HeroLaraToolkit\HealthCheck\Http\Controllers;

use HeroLaraToolkit\HealthCheck\Checks\ExternalApiCheck;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdaptersHealthController
{
    public function checkAdapter(string $name, ExternalApiCheck $externalApiCheck): JsonResponse
    {
        $check = $externalApiCheck->setName($name)
            ->getAdaptersNames(scandir(config('healthcheckhero.health.adapters.path')))
            ->verifyAdapter()
            ->setUrl()
            ->run();

        if (isset($check->status) && $check->status->value == 'ok') {
            return response()->json([
                'name' => $name,
                'status' => $check->status->value,
                'summary' => $check->shortSummary,
                'meta' => $check->meta,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'name' => $name,
            'status' => $check->status->value,
            'summary' => $check->shortSummary,
            'meta' => $check->meta,
        ], Response::HTTP_BAD_REQUEST);
    }

    public function checkAllAdapters(ExternalApiCheck $externalApiCheck): JsonResponse
    {
        $adapters = scandir(app_path('Adapters'));
        $results = [];
        foreach ($adapters as $adapter) {
            if (
                ($adapter !== '.' && $adapter !== '..') &&
                !in_array($adapter, config('healthcheckhero.health.adapters.ignore'))
            ) {
                $results[] = $externalApiCheck->setName($adapter)
                    ->getAdaptersNames([$adapter])
                    ->verifyAdapter()
                    ->setUrl()
                    ->run();
            }
        }
        return response()->json($results, Response::HTTP_OK);
    }
}
