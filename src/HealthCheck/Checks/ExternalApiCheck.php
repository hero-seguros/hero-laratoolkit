<?php

namespace HeroLaraToolkit\HealthCheck\Checks;

use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class ExternalApiCheck extends Check
{
    /**
     * @var string
     */
    protected ?string $name = null;

    /**
     * @var object|null
     */
    protected ?object $adapterInstance = null;

    /**
     * @var string
     */
    protected ?string $url = null;

    /**
     * @var array
     */
    protected array $adapters = [];

    /**
     * Set the name of the adapter
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Run the check
     * @return Result
     */
    public function run(): Result
    {
        try {
            if ($this->url === null || $this->adapterInstance === null) {
                return Result::make()
                    ->failed("Adapter {$this->name} não encontrado ou URL não configurada")
                    ->meta([
                        'name' => $this->name,
                        'url' => $this->url,
                    ]);
            }

            $response = Http::timeout(10)->get($this->url);

            if ($response->successful() || $response->status() === 404) {
                return Result::make()
                    ->ok()
                    ->shortSummary("API {$this->name} respondendo")
                    ->meta([
                        'url' => $this->url,
                        'status' => $response->status()
                    ]);
            }

            return Result::make()
                ->failed("Falha ao acessar API {$this->name}")
                ->meta([
                    'url' => $this->url,
                    'status' => $response->status()
                ]);
        } catch (\Throwable $e) {
            return Result::make()
                ->failed("Falha ao acessar API ou Adapter inexistente {$this->name}")
                ->meta([
                    'exception' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Get the names of the adapters
     * @param array $adapters
     * @param array $names
     * @return self
     */
    public function getAdaptersNames(array $adapters, array $names = []): self
    {
        foreach ($adapters as $adapter) {
            if ($adapter !== '.' && $adapter !== '..') {
                if (is_dir(config('healthcheckhero.health.adapters.path') . '/' . $adapter)) {
                    $this->getAdaptersNames(scandir(config('healthcheckhero.health.adapters.path') . '/' . $adapter), $names);
                } else {
                    $this->adapters[] = [
                        'name' => substr(strtolower($adapter), 0, -4),
                        'originalName' => $adapter,
                    ];
                }
            }
        }

        return $this;
    }

    /**
     * Set the url of the adapter
     * @return self
     */
    public function setUrl(): self
    {
        if ($this->adapterInstance !== null) {
            $this->url = $this->adapterInstance->getUrl();
        }
        return $this;
    }

    /**
     * Verify the adapter
     * @return self
     */
    public function verifyAdapter(): self
    {
        $normalizedName = strtolower($this->name);
        if (substr($normalizedName, -4) === '.php') {
            $normalizedName = substr($normalizedName, 0, -4);
        }

        foreach ($this->adapters as $adapter) {
            if ($adapter['name'] == $normalizedName) {
                $this->adapterInstance = $this->getAdapterInstance($adapter['originalName']);
                return $this;
            }
        }

        return $this;
    }

    /**
     * Get the instance of the adapter
     * @param string $adapterName
     * @return object
     */
    public function getAdapterInstance(string $adapterName): object
    {
        $adapterClassName = config('healthcheckhero.health.adapters.namespace') . '\\' . substr($adapterName, 0, -4);
        return new $adapterClassName();
    }
}
