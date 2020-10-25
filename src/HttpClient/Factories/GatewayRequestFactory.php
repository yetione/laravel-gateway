<?php


namespace Yetione\Gateway\HttpClient\Factories;


use Yetione\Gateway\Http\Request;
use Yetione\Gateway\Logging\LogManager;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Options\HttpClientOptions;
use Laravel\Lumen\Application;
use Symfony\Component\HttpFoundation\HeaderBag;

class GatewayRequestFactory extends RequestFactory
{
    protected GatewayOptions $gatewayOptions;

    protected Request $request;

    public function __construct(Request $request, HttpClientOptions $httpClientOptions, Application $app, GatewayOptions $gatewayOptions)
    {
        parent::__construct($request, $httpClientOptions, $app);
        $this->gatewayOptions = $gatewayOptions;
    }

    protected function createHeaders(): HeaderBag
    {
        $requestHeaders = parent::createHeaders();
        $requestHeaders->set('X-Gateway-Url', $this->gatewayOptions->getStrict('domain'));
        return $requestHeaders;
    }
}
