<?php

namespace App\Console\Commands;

use App\Models\CronJobLog;

trait LogsExecution
{
    protected ?CronJobLog $currentLog = null;
    protected array $logOutputs = [];
    protected float $startTimeFloat = 0;

    protected function startLogging(): void
    {
        $this->startTimeFloat = microtime(true);
        $this->currentLog = CronJobLog::create([
            'command' => $this->signature ?? $this->getName(),
            'status' => 'running',
            'started_at' => now(),
        ]);
        $this->logOutputs = [];
    }

    protected function finishLogging(string $status, ?string $error = null): void
    {
        if ($this->currentLog) {
            $durationMs = (int)((microtime(true) - $this->startTimeFloat) * 1000);
            
            $this->currentLog->update([
                'status' => $status,
                'completed_at' => now(),
                'duration_ms' => $durationMs,
                'output' => implode("\n", $this->logOutputs),
                'error' => $error,
            ]);
        }
    }

    public function info($string, $verbosity = null)
    {
        $this->logOutputs[] = "[" . now()->format('Y-m-d H:i:s') . "] [INFO] " . $string;
        parent::info($string, $verbosity);
    }

    public function error($string, $verbosity = null)
    {
        $this->logOutputs[] = "[" . now()->format('Y-m-d H:i:s') . "] [ERROR] " . $string;
        parent::error($string, $verbosity);
    }
}
