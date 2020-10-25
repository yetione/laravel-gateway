<?php

namespace Yetione\Gateway\Providers;

use Yetione\Gateway\Http\Request;
use Yetione\Gateway\HttpClient\Contracts\HttpRequestContract;
use Yetione\Gateway\HttpClient\Factories\GatewayHttpClientFactory;
use Yetione\Gateway\HttpClient\Factories\GatewayRequestFactory;
use Yetione\Gateway\HttpClient\Factories\HttpClientFactory;
use Yetione\Gateway\HttpClient\Factories\RequestFactory;
use Yetione\Gateway\HttpClient\HttpRequest;
use Yetione\Gateway\HttpClient\RestClient;
use Yetione\Gateway\Options\DefaultServiceRouteOptions;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Options\HttpClientOptions;
use Yetione\Gateway\Options\Resolvers\ResolverConfigurator;
use Yetione\Gateway\Options\ServiceOptions;
use Yetione\Gateway\Options\ServiceRouteOptions;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Yetione\Gateway\Routing\DefaultServiceRoute;
use Yetione\Gateway\Routing\HostResolvers\RandomHostResolver;
use Yetione\Gateway\Services\RouteRegistry;
use Yetione\Gateway\Services\RoutingService;
use Yetione\Gateway\Routing\Service;
use Yetione\Gateway\Services\ServiceFactory;
use Yetione\Gateway\Services\ServiceRegistry;
use Yetione\Gateway\Routing\ServiceRoute;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Monolog\Logger;
use Monolog\Processor\TagProcessor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ResolverConfigurator::class);
        $this->app->singleton(RequestFactory::class);
        $this->app->singleton(GatewayRequestFactory::class);
        $this->app->singleton(HttpClientFactory::class);
        $this->app->singleton(GatewayHttpClientFactory::class);

        $this->app->singleton(RestClient::class);

        $this->app->bind(ServiceContract::class, Service::class);
        $this->app->bind(ServiceRouteContract::class, ServiceRoute::class);
        $this->app->bind(DefaultServiceRoute::class);

        $this->app->singleton(RouteRegistry::class);

        $this->app->singleton(ServiceFactory::class);

        $this->app->singleton(ServiceRegistry::class);

        $this->app->singleton(RoutingService::class);

        $this->app->singleton(HostResolverContract::class, RandomHostResolver::class);

        $this->app->singleton(Request::class, function() {
            return $this->prepareRequest(Request::capture());
        });
        $this->app->alias(Request::class, 'request');
        $this->app->singleton(HttpClientOptions::class);
        $this->app->when(HttpClientOptions::class)->needs('$options')->give(function() {
            return config('http-client');
        });
        $this->app->singleton(GatewayOptions::class);
        $this->app->when(GatewayOptions::class)
            ->needs('$options')
            ->give(function() {
                return config('gateway.globals');
            });

        $this->app->bind(ServiceOptions::class);
        $this->app->bind(ServiceRouteOptions::class);
        $this->app->bind(DefaultServiceRouteOptions::class);

        $this->app->bind(HttpRequestContract::class, HttpRequest::class);

        $this->app->resolving(LogManager::class, function (LogManager $logManager) {
            $logManager->extend('daily', function (Application $app, array $config) {
                /** @var LogManager $this */
                /** @var Logger $logger */
                $logger = $this->createDailyDriver($config);
                /** @var Request $request */
                $request = $app->make(Request::class);
                $tagProcessor = new TagProcessor();
                $logger->pushProcessor($tagProcessor);
                return $logger;
            });
        });
    }

    public function boot()
    {
        /** @var RoutingService $routingService */
        try {
            $routingService = $this->app->make(RoutingService::class);
            $routingService->registerRoutes();
        } catch (BindingResolutionException $e) {
            Log::error('Error when register routes.', ['message'=>$e->getMessage(), 'code'=>$e->getCode()]);
        }

    }

    protected function prepareRequest(Request $request)
    {
        $request
            ->setUserResolver(function () {
            return $this->app->make('auth')->user();
        })
            ->setRouteResolver(function () {
            return $this->app->currentRoute;
        })
//            ->setTrustedProxies([
//            '10.7.0.0/16', // Docker Cloud
//            '103.21.244.0/22', // Cloud Flare
//            '103.22.200.0/22',
//            '103.31.4.0/22',
//            '104.16.0.0/12',
//            '108.162.192.0/18',
//            '131.0.72.0/22',
//            '141.101.64.0/18',
//            '162.158.0.0/15',
//            '172.64.0.0/13',
//            '173.245.48.0/20',
//            '188.114.96.0/20',
//            '190.93.240.0/20',
//            '197.234.240.0/22',
//            '198.41.128.0/17',
//            '199.27.128.0/21',
//            '172.31.0.0/16', // Rancher
//            '10.42.0.0/16' // Rancher
//        ], \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL)
        ;
        $request::setTrustedProxies(['172.28.0.0/24'], Request::HEADER_X_FORWARDED_ALL);

        return $request;
    }
}
