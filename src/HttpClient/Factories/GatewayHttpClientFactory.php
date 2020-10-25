<?php


namespace Yetione\Gateway\HttpClient\Factories;


use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Options\HttpClientOptions;

class GatewayHttpClientFactory extends HttpClientFactory
{
    protected GatewayOptions $gatewayOptions;

    public function __construct(HttpClientOptions $httpClientOptions, GatewayOptions $gatewayOptions, GatewayRequestFactory $requestFactory)
    {
        parent::__construct($httpClientOptions, $requestFactory);
        $this->gatewayOptions = $gatewayOptions;
        $this->defaultClientOptions = array_merge(
            $this->defaultClientOptions,
            $this->gatewayOptions->getStrict('http_client_options'),
            $this->gatewayOptions->getStrict('request_options'),
        );
    }
}
