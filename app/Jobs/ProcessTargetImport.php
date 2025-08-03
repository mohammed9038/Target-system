<?php

namespace App\Jobs;

use App\Services\TargetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTargetImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $targetsData;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $targetsData, int $userId)
    {
        $this->targetsData = $targetsData;
        $this->userId = $userId;
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(TargetService $targetService): void
    {
        Log::info('Starting target import job', [
            'user_id' => $this->userId,
            'total_records' => count($this->targetsData)
        ]);

        try {
            $results = $targetService->importTargets($this->targetsData);
            
            Log::info('Target import completed', [
                'user_id' => $this->userId,
                'results' => $results
            ]);
            
            // Here you could notify the user via email, push notification, etc.
            
        } catch (\Exception $e) {
            Log::error('Target import failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Target import job failed', [
            'user_id' => $this->userId,
            'exception' => $exception->getMessage()
        ]);
    }
}
