<?php


namespace Yetione\Gateway\Auth;


use GuzzleHttp\Promise\Utils;
use Yetione\Gateway\Enums\HTTPRequestType;
use Yetione\Gateway\Exceptions\UnableToExecuteRequestException;
use Yetione\Gateway\HttpClient\Contracts\HttpClientContract;
use Yetione\Gateway\HttpClient\Contracts\HttpClientFactoryContract;
use Yetione\Gateway\HttpClient\Contracts\HttpRequestContract;
use Yetione\Gateway\HttpClient\Factories\HttpClientFactory;
use Yetione\Gateway\Options\PassportServiceOptions;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Yetione\Json\Exceptions\JsonException;
use Yetione\Json\Json;

class TokenProvider implements UserProvider
{
    protected array $config;

    protected HttpClientFactoryContract $clientFactory;

    protected HttpClientContract $httpClient;

    protected PassportServiceOptions $passportServiceOptions;

    public function __construct(HttpClientFactory $clientFactory, PassportServiceOptions $passportServiceOptions)
    {
        $this->clientFactory = $clientFactory;
        $this->passportServiceOptions = $passportServiceOptions;
        $this->httpClient = $clientFactory->client([]);
    }

    public function retrieveById($identifier)
    {
    }

    /**
     * @param string $header
     * @return GatewayUser|null
     */
    public function retrieveByHeader(string $header)
    {
        $startLoading = microtime(true);
        $request = $this->httpClient->requestFactory()->make(
            HTTPRequestType::GET,
            $this->passportServiceOptions->endpointUri('retrieveByHeader'),
            $this->passportServiceOptions->getStrict('endpoints.retrieveByHeader.request_options')
        );
        Log::debug('Request created.', ['time'=>microtime(true)-$startLoading]);

        $request->headers()->set('Authorization', $header);
        $response = $this->loadSync($request);
        if (200 !== $response->getStatusCode()) {
            Log::warning('Authentication error! Invalid response coed.', [
                'response_code'=>$response->getStatusCode(),
                'request_options'=>$request->buildOptions(),
            ]);
            return null;
        }
        $startDecoding = microtime(true);
        $body = $response->getBody()->getContents();
        try {
            $parsedResponse = Json::decode($body, true);
            Log::debug('End decoding.', ['time'=>microtime(true) - $startDecoding]);
        } catch (JsonException $e) {
            Log::error('Authentication error! Response parsing failed.', [
                'message'=>$e->getMessage(),
                'code'=>$e->getCode(),
                'body'=>$e->value
            ]);
            return null;
        }
        Log::debug('Authentication success!', [
            'time'=> microtime(true) - $startLoading,
            'result'=>$parsedResponse,
        ]);
        return new GatewayUser($parsedResponse['user']);
    }

    protected function loadSync(HttpRequestContract $request): ?ResponseInterface
    {
        try {
            $response = $this->httpClient->send($request);
        } catch (UnableToExecuteRequestException | GuzzleException $e) {
            Log::error('Authentication error! Request failed.', [
                'message'=>$e->getMessage(),
                'code'=>$e->getCode(),
                'request_options'=>$request->buildOptions(),
            ]);
            return null;
        }
        return $response;
    }

    protected function loadAsync(HttpRequestContract $request): ?ResponseInterface
    {
        try {
            $promise = $this->httpClient->sendAsync($request);
            $responses = Utils::settle($promise)->wait();
        } catch (UnableToExecuteRequestException | GuzzleException $e) {
            Log::error('Authentication error! Request failed.', [
                'message'=>$e->getMessage(),
                'code'=>$e->getCode(),
                'request_options'=>$request->buildOptions(),
            ]);
            return null;
        }
        /** @var ResponseInterface $response */
        $response = $responses[0]['value'];
        return $response;
    }

    public function retrieveByToken($identifier, $token)
    {
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
    }
}
