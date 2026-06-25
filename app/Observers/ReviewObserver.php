<?php

namespace App\Observers;

use App\Jobs\RecalculateTenantRating;
use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        RecalculateTenantRating::dispatch($review->tenant_id);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        RecalculateTenantRating::dispatch($review->tenant_id);
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        RecalculateTenantRating::dispatch($review->tenant_id);
    }
}