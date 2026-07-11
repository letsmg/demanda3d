<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Dispute;
use App\Models\Input;
use App\Models\Message;
use App\Models\Order;
use App\Models\Thread;
use App\Policies\ClientPolicy;
use App\Policies\DisputePolicy;
use App\Policies\InputPolicy;
use App\Policies\MessagePolicy;
use App\Policies\OrderPolicy;
use App\Policies\ThreadPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class,
        Dispute::class => DisputePolicy::class,
        Input::class => InputPolicy::class,
        Message::class => MessagePolicy::class,
        Order::class => OrderPolicy::class,
        Thread::class => ThreadPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
