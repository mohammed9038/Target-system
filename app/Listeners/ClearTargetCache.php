<?php

namespace App\Listeners;

use App\Events\TargetUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClearTargetCache implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TargetUpdated $event): void
    {
        // Clear related caches
        Cache::tags(['targets', 'matrix'])->flush();
        
        // Clear specific cache keys
        Cache::forget('target_statistics');
        Cache::forget('matrix_data_' . md5(serialize([])));
        
        Log::info('Target cache cleared after update', [
            'target_id' => $event->target->id,
            'salesman' => $event->target->salesman->name ?? 'Unknown',
        ]);
    }
}
