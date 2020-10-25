<?php


namespace Yetione\Gateway\HttpClient\Factories;


use Yetione\Gateway\HttpClient\Contracts\HttpClientContract;
use Yetione\Gateway\HttpClient\Contracts\HttpClientFactoryContract;
use Yetione\Gateway\HttpClient\Contracts\RequestFactoryContract;
use Yetione\Gateway\HttpClient\HttpClient;
use Yetione\Gateway\Options\HttpClientOptions;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class HttpClientFactory implements HttpClientFactoryContract
{
    protected HttpClientContract $client;

    protected HttpClientOptions $httpClientOptions;

    protected RequestFactoryContract $requestFactory;

    protected array $defaultClientOptions;

    public function __construct(HttpClientOptions $httpClientOptions, RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
        $this->httpClientOptions = $httpClientOptions;
        $this->defaultClientOptions = array_merge(
            $this->httpClientOptions->getStrict('client_options'),
            $this->httpClientOptions->getStrict('request_options')
        );
    }

    public function make(array $options): HttpClientContract
    {
        return new HttpClient($this->createGuzzleClient($options), $this->requestFactory);
    }

    public function client(array $options=[], bool $create=false): HttpClientContract
    {
        if (!isset($this->client) || $create) {
            $this->client = $this->make($options);
        }
        return $this->client;
    }

    protected function createGuzzleClient(array $options): ClientInterface
    {
        return new Client(array_merge($this->defaultClientOptions, $options));
    }
}
