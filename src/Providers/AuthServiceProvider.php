<?php

namespace Yetione\Gateway\Providers;

use Yetione\Gateway\Auth\TokenGuard;
use Yetione\Gateway\Auth\TokenProvider;
use Yetione\Gateway\HttpClient\Factories\HttpClientFactory;
use Yetione\Gateway\Options\PassportServiceOptions;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PassportServiceOptions::class);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.



        Auth::provider('remote_service', function (Application $app, array $config) {
            return new TokenProvider($app->make(HttpClientFactory::class), $app->make(PassportServiceOptions::class, ['options'=>$config['service']]));
        });

        Auth::extend('header', function (Application $app, $name, array $config) {
            return new TokenGuard($app->make('request'), Auth::createUserProvider($config['provider']));
        });
    }
}
