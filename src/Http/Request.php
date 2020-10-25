<?php


namespace Yetione\Gateway\Http;


use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;

class Request extends \Illuminate\Http\Request
{
    protected ?ServiceRouteContract $currentRoute;

    public function attachRoute(?ServiceRouteContract $route): self
    {
        $this->currentRoute = $route;
        return $this;
    }

    public function getRoute(): ?ServiceRouteContract
    {
        return $this->currentRoute;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        $route = call_user_func($this->getRouteResolver());

        return $route ? $route[2] : [];
    }

    public function route($param = null, $default = null)
    {
        $route = call_user_func($this->getRouteResolver());

        if (is_null($route) || is_null($param)) {
            return $route;
        }
        return $route[2][$param] ?? $default;
    }
}
