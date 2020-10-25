<?php


namespace Yetione\Gateway\Services;

use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Illuminate\Support\Collection;

class RouteRegistry
{
    /**
     * @var ServiceRouteContract[]
     */
    protected array $items = [];

    public function add(ServiceRouteContract $route): self
    {
        $this->items[$route->id()] = $route;
        return $this;
    }

    public function get(string $id): ?ServiceRouteContract
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    public function has(string $id): bool
    {
        return null !== $this->get($id);
    }

    public function all(): Collection
    {
        return collect($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
