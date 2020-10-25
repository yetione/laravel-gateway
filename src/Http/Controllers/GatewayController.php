<?php


namespace Yetione\Gateway\Http\Controllers;


use Yetione\Gateway\Enums\HTTPRequestType;
use Yetione\Gateway\Exceptions\DataFormatException;
use Yetione\Gateway\Http\Request;
use Yetione\Gateway\HttpClient\Contracts\HttpClientContract;
use Yetione\Gateway\HttpClient\Factories\GatewayHttpClientFactory;
use Yetione\Gateway\HttpClient\RestClient;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Services\ServiceRegistry;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait GatewayController
{
    protected ServiceRegistry $serviceRegistry;

    protected Request $request;

    protected GatewayOptions $gatewayOptions;

    protected GatewayHttpClientFactory $httpClientFactory;

    protected HttpClientContract $httpClient;

    /**
     * GatewayController constructor.
     * @param Request $request
     * @param RestClient $restClient
     * @param ServiceRegistry $serviceRegistry
     * @param GatewayOptions $gatewayOptions
     * @param GatewayHttpClientFactory $httpClientFactory
     * @throws DataFormatException
     */
    public function __construct(Request $request, ServiceRegistry $serviceRegistry, GatewayOptions $gatewayOptions, GatewayHttpClientFactory $httpClientFactory)
    {
        if (empty($request->getRoute())) {
            throw new DataFormatException('Unable to find original URI pattern');
        }
        $this->request = $request;
        $this->serviceRegistry = $serviceRegistry;
        $this->gatewayOptions = $gatewayOptions;
        $this->httpClientFactory = $httpClientFactory;
        $this->httpClient = $this->httpClientFactory->client();
    }

    public function get()
    {
        return $this->processRequest(HTTPRequestType::GET);
    }

    public function post()
    {
        return $this->processRequest(HTTPRequestType::POST);
    }

    public function put()
    {
        return $this->processRequest(HTTPRequestType::PUT);
    }

    public function delete()
    {
        return $this->processRequest(HTTPRequestType::DELETE);
    }

    public function patch()
    {
        return $this->processRequest(HTTPRequestType::PATCH);
    }

    public function head()
    {
        return $this->processRequest(HTTPRequestType::HEAD);
    }

    public function options()
    {
        return $this->processRequest(HTTPRequestType::OPTIONS);
    }

    protected function processRequest(string $method): Response
    {
        $response = $this->simpleRequest($method);
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $headers = $response->getHeaders();
        Log::debug($method.' response', [
            'status'=>$statusCode,
            'body'=>$body,
            'headers'=>$headers
        ]);
        return response()->make($body, $statusCode, $headers);
    }

    protected function simpleRequest(string $method)
    {
        $routeParamName = $this->gatewayOptions->get('route_param_name');
        // TODO: Refactor
        $parametersJar = array_merge(
            [$routeParamName=>$this->request->route($routeParamName, '')],
            $this->request->getRouteParams(),
            ['query_string'=> $this->request->getQueryString()]
        );
        $serviceRoute = $this->request->getRoute();
        $request = $this->httpClient
            ->requestFactory()
            ->setRequestClass($serviceRoute->requestClass())->make(
                $method,
                $serviceRoute->serviceUri($parametersJar),
                array_merge($serviceRoute->service()->requestOptions(), $serviceRoute->requestOptions())
            );
        if (!$serviceRoute->public()) {
            $request->setUser($this->request->user());
        }
        Log::debug($method.' request', [
            'url'=>$request->url(),
            'request_params'=>$request->buildOptions(),
            'parameters'=>$parametersJar
        ]);
        return $this->httpClient->send($request);
    }
}
