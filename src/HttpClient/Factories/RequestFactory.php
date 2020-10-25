<?php


namespace Yetione\Gateway\HttpClient\Factories;


use Yetione\Gateway\Enums\HTTPRequestType;
use Yetione\Gateway\Exceptions\GatewayRequestException;
use Yetione\Gateway\Http\Request;
use Yetione\Gateway\HttpClient\Contracts\HttpRequestContract;
use Yetione\Gateway\HttpClient\Contracts\RequestFactoryContract;
use Yetione\Gateway\HttpClient\HttpRequest;
use Yetione\Gateway\Logging\LogManager;
use Yetione\Gateway\Options\HttpClientOptions;
use Laravel\Lumen\Application;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;

class RequestFactory implements RequestFactoryContract
{
    protected Request $request;

    protected HeaderBag $defaultHeaders;

    protected HttpClientOptions $httpClientOptions;

    protected Application $app;

    protected string $requestClass;

    public function __construct(Request $request, HttpClientOptions $httpClientOptions, Application $app)
    {
        $this->request = $request;
        $this->httpClientOptions = $httpClientOptions;
        $this->app = $app;
        $this->requestClass = HttpRequest::class;
    }

    public function make(string $method, string $uri, array $options=[]): HttpRequestContract
    {
        if (!HTTPRequestType::isValid($method)) {
            throw new GatewayRequestException(sprintf('Method [%s] is not valid.', $method));
        }
        $request = $this->createRequest($method, $uri, $options);
        $request->headers()->add($this->getDefaultHeaders()->all());
        return $request;
    }

    protected function createRequest(string $method, string $uri, array $options=[])
    {
        return $this->app->make($this->requestClass(), [
            'method'=>$method,
            'url'=>$uri,
            'options'=>$options
        ]);
    }

    public function requestClass(): string
    {
        return $this->requestClass;
    }

    public function setRequestClass(string $requestClass): RequestFactoryContract
    {
        $this->requestClass = $requestClass;
        return $this;
    }

    protected function getDefaultHeaders(): HeaderBag
    {
        if (!isset($this->defaultHeaders)) {
            $this->defaultHeaders = $this->createHeaders();
        }
        return $this->defaultHeaders;
    }

    protected function createHeaders(): HeaderBag
    {
        $skippedHeaders = array_merge($this->httpClientOptions->get('skipped_headers', []), $this->httpClientOptions->get('hop_by_hop_headers'));
        if (!empty($connectionHeader = $this->request->headers->get('Connection'))) {
            // Парсим hop-by-hop заголовки из заголовка Connection. Они указываются через запятую, после типа соединения
            if (1 < count($connectionHeader = (array) explode(',', $connectionHeader))) {
                $skippedHeaders = array_merge($skippedHeaders, array_map('trim', array_slice($connectionHeader, 1)));
            }
        }
        $skippedHeaders = new HeaderBag(array_flip($skippedHeaders));
        $requestHeaders = new HeaderBag($this->request->headers->all());
        foreach ($requestHeaders->keys() as $header) {
            if ($skippedHeaders->has($header)) {
                $requestHeaders->remove($header);
            }
        }
        /** @var LogManager $logManager */
        $logManager = $this->app->make(LoggerInterface::class);
        $requestHeaders->add([
            'X-Client-Ip' => $this->request->getClientIp(),
            'X-Request-Id'=> $logManager->getTagProcessor()->getRequestId()
        ]);
        return $requestHeaders;
    }

    public function get(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::GET, $uri, $options);
    }

    public function post(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::POST, $uri, $options);
    }

    public function put(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::PUT, $uri, $options);
    }

    public function patch(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::PATCH, $uri, $options);
    }

    public function delete(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::DELETE, $uri, $options);
    }

    public function head(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::HEAD, $uri, $options);
    }

    public function options(string $uri, array $options=[])
    {
        return $this->make(HTTPRequestType::OPTIONS, $uri, $options);
    }
}
