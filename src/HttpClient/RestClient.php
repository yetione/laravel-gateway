<?php


namespace Yetione\Gateway\HttpClient;


use Yetione\Gateway\Exceptions\InvalidUri;
use Yetione\Gateway\Exceptions\UnableToExecuteRequestException;
use Yetione\Gateway\Http\Request;
use Yetione\Gateway\HttpClient\Contracts\HttpClientContract;
use Yetione\Gateway\HttpClient\Factories\HttpClientFactory;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Laravel\Lumen\Routing\UrlGenerator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise;

class RestClient
{
    /**
     * @var Client|ClientInterface
     */
    protected HttpClientContract $client;

    protected array $requestParams;

    protected Request $request;

    protected GatewayOptions $gatewayOptions;

    public function __construct(HttpClientFactory $httpClientFactory, Request $request, GatewayOptions $gatewayOptions)
    {
        $this->client = $httpClientFactory->make([]);
        $this->request = $request;
        $this->gatewayOptions = $gatewayOptions;
        $this->clearRequest();
    }

    public function execute(HttpRequest $request)
    {
        $options = $request->options();
        if (!$request->headers()->isEmpty()) {
        }
    }

    protected function injectHeaders()
    {
        $headers = [
            'X-Client-Ip' => $this->request->getClientIp(),
            'User-Agent'=> $this->request->header('User-Agent'),
            'Content-Type'=> 'application/json',
            'Accept' => 'application/json',
            'X-Gateway-Url'=>$this->gatewayOptions->getStrict('domain')
        ];
        if ($this->request->headers->has('Authorization')) {
            $headers['Authorization'] = $this->request->header('Authorization');
        }
//        if (null !== ($user = $this->request->user())) {
//            $headers = array_merge($headers, [
//                'X-User'=> $user->id,
//                'X-Token-Scopes'=> implode(',', $user->token()->scopes)
//            ]);
//        }
        $this->setHeaders($headers);
    }

    public function setHeaders(array $headers): self
    {
        $this->requestParams['headers'] = $headers;
        return $this;
    }

    public function setUser($user)
    {
        $this->requestParams['headers']['X-User'] = $user->id;
        return $this;
    }

    public function setContentType(string $contentType): self
    {
        $this->requestParams['headers']['Content-Type'] = $contentType;
        return $this;
    }

    public function setContentSize($contentSize): self
    {
        $this->requestParams['headers']['Content-Length'] = $contentSize;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->requestParams['headers'];
    }

    public function setBody($body): self
    {
        $this->requestParams['body'] = $body;
        return $this;
    }

    public function setFiles($files)
    {
        $this->setHeaders(array_intersect_key(
            $this->getHeaders(),
            ['X-User' => null, 'X-Token-Scopes' => null]
        ));

        if (isset($this->requestParams['body'])) {
            unset($this->requestParams['body']);
        }

        $this->requestParams['timeout'] = 30;
        $this->requestParams['multipart'] = [];

        foreach ($files as $key => $file) {
            $this->requestParams['multipart'][] = [
                'name'=>$key,
                'contents'=>fopen($file->getRealPath(), 'r'),
                'filename'=>$file->getClientOriginalName(),
            ];
        }

        return $this;
    }

    /**
     * @param string $method
     * @param ServiceRouteContract $route
     * @param $parametersJar
     * @return ResponseInterface|null
     * @throws UnableToExecuteRequestException
     */
    public function syncRequest(string $method, ServiceRouteContract $route, $parametersJar)
    {
        $this->requestParams = array_merge(
            $route->service()->requestOptions(),
            $route->requestOptions(),
            $this->requestParams
        );
        try {
            /** @var ResponseInterface $response */
            $response = $this->{strtolower($method)}(
                $route->serviceUri($parametersJar)
            );
        } catch (ConnectException $e) {
            throw new UnableToExecuteRequestException();
        } catch (RequestException $e) {
            return $e->getResponse();
        }
        return $response;
    }

    /**
     * @param string $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post($url)
    {
        Log::debug('POST request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->post($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function put($url)
    {
        Log::debug('PUT request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->put($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get($url)
    {
        Log::debug('GET request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->get($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete($url)
    {
        Log::debug('DELETE request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->delete($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function patch($url)
    {
        Log::debug('PATCH request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->patch($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function head($url)
    {
        Log::debug('HEAD request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->head($url, $this->requestParams);
    }

    /**
     * @param $url
     * @return mixed
     */
    public function options($url)
    {
        Log::debug('OPTIONS request', [
            'url'=>$url,
            'request_params'=>$this->requestParams
        ]);
        return $this->client->options($url, $this->requestParams);
    }



    public function asyncRequest(Collection $batch, $parametersJar)
    {
        $wrapper = new RestBatchResponse();
        $wrapper->setCritical($batch->filter(function($action) { return $action->isCritical(); })->count());
        $promises = $batch->reduce(function($carry, $action) use ($parametersJar) {
            $method = strtolower($action->getMethod());
            $url = $this->buildUrl($action, $parametersJar);

            // Get body parameters for the current request
            $bodyAsync = $action->getBodyAsync();

            if (!is_null($bodyAsync)) {
                $this->setBody(json_encode($this->injectBodyParams($bodyAsync, $parametersJar)));
            }

            $carry[$action->getAlias()] = $this->client->{$method . 'Async'}($url, $this->requestParams);

            return $carry;
        }, []);

        return $this->processResponses(
            $wrapper,
            collect(Promise\settle($promises)->wait())
        );
    }

    private function processResponses(RestBatchResponse $wrapper, Collection $responses)
    {
        // Process successful responses
        $responses->filter(function ($response) {
            return $response['state'] == 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {

            $wrapper->addSuccessfulAction($alias, $response['value']);
        });

        // Process failures
        $responses->filter(function ($response) {
            return $response['state'] != 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {
            $response = $response['reason']->getResponse();

            if ($wrapper->hasCriticalActions()) throw new UnableToExecuteRequestException($response);

            // Do we have an error response from the service?
            if (! $response) $response = new PsrResponse(502, []);
            $wrapper->addFailedAction($alias, $response);
        });

        return $wrapper;
    }

    /**
     * @param array $body
     * @param array $params
     * @param string $prefix
     * @return array
     */
    private function injectBodyParams(array $body, array $params, $prefix = '')
    {
        foreach ($params as $key => $value) {
            foreach ($body as $bodyParam => $bodyValue) {
                if (is_string($value) || is_numeric($value)) {
                    $body[$bodyParam] = str_replace("{" . $prefix . $key . "}", $value, $bodyValue);
                } else if (is_array($value)) {
                    if ($bodyValue == "{" . $prefix . $key . "}") {
                        $body[$bodyParam] = $value;
                    }
                }
            }
        }
        return $body;
    }

    public function clearRequest()
    {
        $this->requestParams = [
            'headers'=> [],
            'timeout'=> 40,
        ];
        $this->injectHeaders();
    }
}
