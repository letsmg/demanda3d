<?php

namespace App\Jobs;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecalculateTenantRating implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $tenantId,
    ) {}

    public function handle(): void
    {
        $stats = Review::where('tenant_id', $this->tenantId)
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        DB::table('tenants')
            ->where('id', $this->tenantId)
            ->update([
                'rating_average' => $stats->average ? round((float) $stats->average, 2) : 0,
                'rating_count' => $stats->count ?? 0,
            ]);
    }
}