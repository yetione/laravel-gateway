<?php


namespace Yetione\Gateway\Services;


use Yetione\Gateway\Http\Controllers\GatewayController;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Application;

class RoutingService
{
    protected ServiceRegistry $serviceRegistry;

    protected RouteRegistry $routeRegistry;

    protected Application $app;

    protected GatewayOptions $gatewayOptions;

    public function __construct(ServiceRegistry $serviceRegistry, RouteRegistry $routeRegistry, GatewayOptions $gatewayOptions, Application $app)
    {
        $this->serviceRegistry = $serviceRegistry;
        $this->routeRegistry = $routeRegistry;
        $this->gatewayOptions = $gatewayOptions;
        $this->app = $app;
        Log::debug('Gateway options', $this->gatewayOptions->all()->toArray());
    }

    public function registerRoutes()
    {
        $this->serviceRegistry->all()
            ->each(function (ServiceContract $service) {
                foreach ($service->routes() as $serviceRoute) {
                    $this->registerServiceRoute($serviceRoute);
                }
                if ($defaultRoute = $service->defaultRoute()) {
                    $this->registerServiceRoute($defaultRoute);
                }
            });
    }

    public function registerServiceRoute(ServiceRouteContract $serviceRoute)
    {
        $middleware = ['service:'.$serviceRoute->id()];
        if (!$serviceRoute->public()) {
            $middleware[] = 'auth';
        }
        $middleware = array_merge($middleware, $serviceRoute->middleware());
        $this->routeRegistry->add($serviceRoute);
        if (!is_abs_path($routePath = $serviceRoute->routePath())) {
            $routePath = $this->gatewayOptions->path().$routePath;
        }
        // TODO: Check path
        foreach ($serviceRoute->method() as $method) {
            $method = mb_strtolower($method);
            if (method_exists($this->app->router, $method) && method_exists(GatewayController::class, $method)) {
                $this->app->router->{$method}($routePath, [
                    'uses'=>GatewayController::class.'@'.$method,
                    'middleware'=>$middleware
                ]);
                Log::debug('Route registered.', [
                    'method'=>$method,
                    'path'=>$routePath,
                    'middleware'=>$middleware,
                ]);
            } else {
                Log::error('Can not register route, because method is not exist.', [
                    'method'=>$method,
                    'path'=>$routePath
                ]);
            }
        }
    }
}
