<?php


namespace Yetione\Gateway\Routing;


use Yetione\Gateway\Options\ServiceOptions;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Closure;

class Service implements ServiceContract
{
    protected ServiceOptions $options;

    protected HostResolverContract $hostResolver;

    /**
     * @var ServiceRouteContract[]
     */
    protected array $routes;

    protected ?ServiceRouteContract $defaultRoute;

    public function __construct(
        ServiceOptions $options,
        HostResolverContract $hostResolver,
        array $routesCallbacks,
        callable $defaultRouteCallback
        )
    {
        $this->options = $options;
        $this->hostResolver = $hostResolver;
        $this->routes = $this->resolveRoutes($routesCallbacks);
        $this->defaultRoute = $defaultRouteCallback($this);
    }

    public function toArray(): array
    {
        return $this->options->all()->merge([
            'routes'=>array_map(
                function (ServiceRouteContract $route): array {
                    return $route->toArray();
                }, $this->routes()
            ),
            'default_route'=>optional($this->defaultRoute())->toArray(),
        ])->toArray();
    }

    protected function resolveRoutes(array $routesCallbacks): array
    {
        return array_map(function (Closure $item): ServiceRouteContract {
            return $item($this);
        }, $routesCallbacks);
    }

    public function id(): string
    {
        return $this->options->getStrict('id');
    }

    public function hostname(): array
    {
        return $this->options->getStrict('hostname');
    }

    public function resolveHost(): string
    {
        return $this->hostResolver->resolve($this->hostname());
    }

    public function getHostResolver(): HostResolverContract
    {
        return $this->hostResolver;
    }

    public function path(): string
    {
        return $this->options->getStrict('path');
    }

    public function method(): array
    {
        return $this->options->getStrict('method');
    }

    public function public(): bool
    {
        return $this->options->getStrict('public');
    }

    public function requestOptions(): array
    {
        return $this->options->getStrict('request_options');
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function defaultRoute(): ?ServiceRouteContract
    {
        return $this->defaultRoute;
    }
}
