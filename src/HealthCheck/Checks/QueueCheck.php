<?php

namespace HeroLaraToolkit\HealthCheck\Checks;

use Illuminate\Support\Facades\DB;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class QueueCheck extends Check
{
    /**
     * @var string
     */
    protected ?string $queue = null;

    /**
     * @var int
     */
    protected int $warningThreshold;

    /**
     * @var int
     */
    protected int $failedThreshold;

    /**
     * Set the warning threshold
     * @param ?int $warningThreshold = null
     * @return self
     */
    public function setWarningThreshold(?int $warningThreshold = null): self
    {
        $this->warningThreshold = $warningThreshold;
        return $this;
    }

    /**
     * Set the failed threshold
     * @param ?int $failedThreshold = null
     * @return self
     */
    public function setFailedThreshold(?int $failedThreshold = null): self
    {
        $this->failedThreshold = $failedThreshold;
        return $this;
    }

    /**
     * Set the queue
     * @param string $queue
     * @return self
     */
    public function setQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Run the check
     * @return Result
     */
    public function run(): Result
    {
        try {
            $pendingCount = $this->getPendingJobsCount();
            $failedCount = $this->getFailedJobsCount();

            $meta = [
                'jobs_pending' => $pendingCount,
                'jobs_failed' => $failedCount,
            ];

            if ($pendingCount >= $this->failedThreshold) {
                return Result::make()
                    ->failed("Fila '{$this->queue}' com {$pendingCount} jobs pendentes e {$failedCount} com falha")
                    ->meta($meta);
            }

            if ($pendingCount >= $this->warningThreshold || $failedCount > 0) {
                return Result::make()
                    ->warning("Fila '{$this->queue}' com {$pendingCount} jobs pendentes e {$failedCount} com falha")
                    ->meta($meta);
            }

            return Result::make()
                ->ok("Fila '{$this->queue}' saudável ({$pendingCount} pendentes, {$failedCount} com falha)")
                ->meta($meta);
        } catch (\Throwable $th) {
            return Result::make()
                ->failed("Falha ao verificar fila '{$this->queue}'")
                ->meta([
                    'exception' => $th->getMessage(),
                ]);
        }
    }

    /**
     * Get the pending jobs count
     * @return int
     */
    protected function getPendingJobsCount(): int
    {
        $query = DB::table('jobs');

        if ($this->queue !== null) {
            $query->where('queue', $this->queue);
        }

        return $query->count();
    }

    /**
     * Get the failed jobs count
     * @return int
     */
    protected function getFailedJobsCount(): int
    {
        $table = config('queue.failed.table', 'failed_jobs');
        $query = DB::table($table);

        if ($this->queue !== null) {
            $query->where('queue', $this->queue);
        }

        return $query->count();
    }

    public function getQueues(?string $param = null): array
    {
        $query = DB::table('jobs')
            ->select('queue')
            ->distinct()
            ->when($param !== null, function ($query) use ($param) {
                $query->where('queue', $param);
            })
            ->get();
        return $query->pluck('queue')->toArray();
    }
}
