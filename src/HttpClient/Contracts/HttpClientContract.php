<?php


namespace Yetione\Gateway\HttpClient\Contracts;


use Yetione\Gateway\Exceptions\UnableToExecuteRequestException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientContract
{
    /**
     * @param HttpRequestContract $request
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws UnableToExecuteRequestException
     */
    public function send(HttpRequestContract $request): ResponseInterface;

    /**
     * @param HttpRequestContract $request
     * @return PromiseInterface
     * @throws UnableToExecuteRequestException
     */
    public function sendAsync(HttpRequestContract $request): PromiseInterface;

    /**
     * @return ClientInterface
     */
    public function client(): ClientInterface;

    /**
     * @param ClientInterface $client
     * @return HttpClientContract
     */
    public function setClient(ClientInterface $client): HttpClientContract;

    /**
     * @return RequestFactoryContract
     */
    public function requestFactory(): RequestFactoryContract;
}
