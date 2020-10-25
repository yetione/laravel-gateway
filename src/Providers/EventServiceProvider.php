<?php

namespace Yetione\Gateway\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Yetione\Gateway\Events\ExampleEvent::class => [
            \Yetione\Gateway\Listeners\ExampleListener::class,
        ],
    ];
}
