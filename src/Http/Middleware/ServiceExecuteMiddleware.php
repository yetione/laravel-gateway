<?php


namespace Yetione\Gateway\Http\Middleware;


use Yetione\Gateway\Http\Request;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Services\RouteRegistry;
use Closure;

class ServiceExecuteMiddleware
{
    protected RouteRegistry $routeRegistry;
    protected GatewayOptions $gatewayOptions;

    public function __construct(RouteRegistry $routeRegistry, GatewayOptions $gatewayOptions)
    {
        $this->routeRegistry = $routeRegistry;
        $this->gatewayOptions = $gatewayOptions;
    }

    public function handle(Request $request, Closure $next, $id)
    {
        $serviceRoute = $this->routeRegistry->get($id);
        $request->attachRoute($serviceRoute);
        $route = $request->route();
        $currentPath = $route[2][$this->gatewayOptions->get('route_param_name')] ?? null;
        if (!empty($serviceRoute->alias())) {
            $route[2][$this->gatewayOptions->get('route_param_name')] = $serviceRoute->path();
        } else {
            $route[2][$this->gatewayOptions->get('route_param_name')] = '/'.ltrim((string)$currentPath, '/');
        }
        $request->setRouteResolver(static function () use ($route) {
            return $route;
        });
        return $next($request);
    }
}
