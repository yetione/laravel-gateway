<?php


namespace Yetione\Gateway\HttpClient;


use Yetione\Gateway\Exceptions\UnableToExecuteRequestException;
use Yetione\Gateway\HttpClient\Contracts\HttpClientContract;
use Yetione\Gateway\HttpClient\Contracts\HttpRequestContract;
use Yetione\Gateway\HttpClient\Contracts\RequestFactoryContract;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpClient implements HttpClientContract
{
    protected ClientInterface $client;

    protected RequestFactoryContract $requestFactory;

    public function __construct(ClientInterface $client, RequestFactoryContract $requestFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function send(HttpRequestContract $request): ResponseInterface
    {
        $startRequest = microtime(true);
        try {
            return $this->client->request($request->method(), $request->url(), $request->buildOptions());
        } catch (ConnectException $e) {
            throw new UnableToExecuteRequestException();
        } catch (RequestException $e) {
            return $e->getResponse();
        } finally {
            Log::debug('Request finished.', ['type'=>'SYNC', 'timing'=>microtime(true)-$startRequest, 'method'=>$request->method(), 'url'=>$request->url()]);
        }
    }

    public function sendAsync(HttpRequestContract $request): PromiseInterface
    {
        $startRequest = microtime(true);
        try {
            return $this->client->requestAsync($request->method(), $request->url(), $request->buildOptions());
        } catch (Throwable $e) {
            throw new UnableToExecuteRequestException();
        } finally {
            Log::debug('Request finished.', ['type'=>'ASYNC', 'timing'=>microtime(true)-$startRequest, 'method'=>$request->method(), 'url'=>$request->url()]);
        }
    }

    public function client(): ClientInterface
    {
        return $this->client;
    }

    public function setClient(ClientInterface $client): HttpClient
    {
        $this->client = $client;
        return $this;
    }

    public function requestFactory(): RequestFactoryContract
    {
        return $this->requestFactory;
    }
}
