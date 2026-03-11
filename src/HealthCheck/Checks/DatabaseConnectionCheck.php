<?php

namespace HeroLaraToolkit\HealthCheck\Checks;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class DatabaseConnectionCheck extends Check
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

            DB::connection($this->connection)->getPdo();

            return Result::make()
                ->ok()
                ->shortSummary("Banco '{$this->connection}' conectado")
                ->meta([
                    'connection' => $this->connection,
                    'driver' => Config::get("database.connections.{$this->connection}.driver"),
                ]);
        } catch (\Throwable $e) {
            return Result::make()
                ->failed("Falha ao conectar no banco '{$this->connection}'")
                ->meta([
                    'connection' => $this->connection,
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

        if (! in_array($this->connection, Config::get('healthcheckhero.health.databases.names'))) {
            return Result::make()
                ->failed("Conexão '{$this->connection}' não existe")
                ->meta([
                    'connection' => $this->connection,
                ]);
        }

        return null;
    }
}
