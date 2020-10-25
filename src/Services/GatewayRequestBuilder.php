<?php


namespace Yetione\Gateway\Services;


use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;

class GatewayRequestBuilder
{
    protected ServiceRouteContract $serviceRoute;

    /**
     * @var false|null|resource|string
     */
    protected $body;

    protected array $files;

    public function __construct(ServiceRouteContract $serviceRoute)
    {
        $this->serviceRoute = $serviceRoute;
    }

    public function body($body): GatewayRequestBuilder
    {
        $this->body = $body;
        return $this;
    }

    public function files(array $files): GatewayRequestBuilder
    {
        $this->files = $files;
        return $this;
    }
}
