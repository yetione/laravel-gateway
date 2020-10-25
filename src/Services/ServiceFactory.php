<?php


namespace Yetione\Gateway\Services;


use Yetione\Gateway\Options\DefaultServiceRouteOptions;
use Yetione\Gateway\Options\ServiceOptions;
use Yetione\Gateway\Options\ServiceRouteOptions;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Yetione\Gateway\Routing\DefaultServiceRoute;
use Closure;
use Laravel\Lumen\Application;

class ServiceFactory
{
    protected Application $app;

    protected string $serviceClass = ServiceContract::class;

    protected string $serviceRouteClass = ServiceRouteContract::class;

    protected string $defaultServiceRouteClass = DefaultServiceRoute::class;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function createServiceOptions(array $options): ServiceOptions
    {
        return $this->app->make(ServiceOptions::class, ['options'=>$options]);
    }

    public function createService(array $options, array $serviceRoutesCallbacks): ServiceContract
    {
        $serviceOptions = $this->createServiceOptions($options);
        return $this->app->make(ServiceContract::class, [
            'options'=>$serviceOptions,
            'routesCallbacks'=>$serviceRoutesCallbacks,
            'defaultRouteCallback'=>false !== $serviceOptions->get('default_route') ?
                $this->createServiceRouteCallback(
                    $this->app->make(DefaultServiceRouteOptions::class, ['options'=>$serviceOptions->get('default_route')]),
                    $this->defaultServiceRouteClass
                ) :
                static function (ServiceContract $service) {return null;},
            'hostResolver'=>$this->app->make(HostResolverContract::class)
        ]);
    }

    public function createServiceRouteOptions(array $options): ServiceRouteOptions
    {
        return $this->app->make(ServiceRouteOptions::class, ['options'=>$options]);
    }

    public function createServiceRoute(ServiceContract $service, ServiceRouteOptions $options): ServiceRouteContract
    {
        return $this->createServiceRouteCallback($options)($service);
    }

    public function createServiceRouteCallback(ServiceRouteOptions $serviceRouteOptions, ?string $routeClass=null): Closure
    {
        $app = $this->app;
        $routeClass = null !== $routeClass && class_exists($routeClass) ? $routeClass : $this->serviceRouteClass;
        return static function (ServiceContract $service) use ($app, $serviceRouteOptions, $routeClass): ServiceRouteContract {
            return $app->make($routeClass, [
                'service'=>$service,
                'options'=>$serviceRouteOptions
            ]);
        };
    }
}
