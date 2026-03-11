<?php

namespace HeroLaraToolkit\HealthCheck\Checks;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class RedisConnectionCheck extends Check
{
    /**
     * @var string
     */
    protected ?string $connection = null;

    /**
     * Set the connection
     * @param string $connection
     * @return self
     */
    public function setConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Run the check
     * @return Result
     */
    public function run(): Result
    {
        try {
            $result = $this->verifyConnection();
            if ($result !== null) {
                return $result;
            }

            $redis = Redis::connection($this->connection);
            $pong = $redis->ping();
            $pongValue = is_object($pong) ? (string) $pong : $pong;

            if ($pongValue !== true && $pongValue !== 'PONG') {
                return Result::make()
                    ->failed("Redis '{$this->connection}' não respondeu corretamente")
                    ->meta([
                        'response' => $pongValue,
                    ]);
            }

            return Result::make()
                ->ok()
                ->shortSummary("Redis '{$this->connection}' conectado")
                ->meta([
                    'connection' => $this->connection,
                    'driver' => Config::get("database.redis.{$this->connection}.host"),
                ]);
        } catch (\Throwable $e) {
            return Result::make()
                ->failed("Falha ao conectar no Redis 2 '{$this->connection}'")
                ->meta([
                    'exception' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Verify the connection
     * @return Result|null
     */
    private function verifyConnection(): Result|null
    {
        if (is_null($this->connection) || empty($this->connection)) {
            return Result::make()
                ->failed("Conexão não foi informada")
                ->meta([
                    'connection' => null,
                ]);
        }

        if (! in_array($this->connection, config('healthcheckhero.health.databases.redisNames'))) {
            return Result::make()
                ->failed("Conexão Redis '{$this->connection}' não existe")
                ->meta([
                    'connection' => $this->connection,
                ]);
        }

        return null;
    }
}
