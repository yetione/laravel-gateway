<?php


namespace Yetione\Gateway\HttpClient;



use Yetione\Gateway\Http\Request;
use Yetione\Gateway\HttpClient\Contracts\HttpRequestContract;
use Yetione\Gateway\Services\Json;
use Yetione\Gateway\User;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\HeaderBag;

class HttpRequest implements HttpRequestContract
{
    protected string $method;

    protected string $url;

    protected Collection $options;

    protected HeaderBag $headers;

    protected CookieJarInterface $cookieJar;

    protected ?User $user = null;

    protected array $files = [];

    protected Request $request;

    public function __construct(Request $request, string $method, string $url, array $options=[])
    {
        $this->method = $method;
        $this->url = $url;
        $this->request = $request;
        $this->headers = new HeaderBag();
        if (isset($options[GuzzleRequestOptions::HEADERS])) {
            $this->headers->add($options[GuzzleRequestOptions::HEADERS]);
            unset($options[GuzzleRequestOptions::HEADERS]);
        }

        $this->cookieJar = new CookieJar();
        if (isset($options[GuzzleRequestOptions::COOKIES])) {
            $this->cookieJar = $options[GuzzleRequestOptions::COOKIES];
            unset($options[GuzzleRequestOptions::COOKIES]);
        }

        $this->options = collect($options);
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): HttpRequest
    {
        $this->user = $user;
        if (null !== $user) {
            $this->headers()->set('X-User', base64_encode($user->toJson()));
        }
        return $this;
    }

    public function headers(): HeaderBag
    {
        return $this->headers;
    }

    public function setHeaders(HeaderBag $headers): HttpRequest
    {
        $this->headers = $headers;
        return $this;
    }

    public function options(): Collection
    {
        return $this->options;
    }

    public function setOptions(Collection $options): HttpRequest
    {
        $this->options = $options;
        return $this;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function cookie(): CookieJarInterface
    {
        return $this->cookieJar;
    }

    public function setCookie(CookieJarInterface $cookieJar): HttpRequestContract
    {
        $this->cookieJar = $cookieJar;
        return $this;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): HttpRequest
    {
        $this->files = $files;
        return $this;
    }

    protected function buildFilesRequest(array $files)
    {
        $this->options->forget([GuzzleRequestOptions::BODY, GuzzleRequestOptions::JSON]);
        // удаляем ненужные заголовки (их за нас добавит http client)
        $this->headers()->remove('Content-Type');
        $this->headers()->remove('Content-Length');
        $multipartForm = [];
        foreach ($files as $key => $file) {
            $multipartForm[] = [
                'name'=>$key,
                'contents'=>fopen($file->getRealPath(), 'r'),
                'filename'=>$file->getClientOriginalName(),
            ];
        }
        $this->options->put(GuzzleRequestOptions::MULTIPART, $multipartForm);
    }

    /**
     * @param array $body
     */
    protected function buildJsonBody(array $body)
    {
        $this->setRequestBody(Json::encode($body));
    }

    /**
     * @param string|resource|StreamInterface|false|null $body
     */
    protected function buildRequestBody($body)
    {
        $this->setRequestBody($body);
    }

    /**
     * @param string|resource|StreamInterface|false|null $body
     * @return HttpRequestContract
     */
    protected function setRequestBody($body): HttpRequestContract
    {
        if (is_resource($body)) {
            if (is_array($stat = fstat($body)) && isset($stat['size'])) {
                $this->setContentLength($stat['size']);
            } elseif (false !== ($body = stream_get_contents($body, -1, 0))) {
                return $this->setRequestBody($body);
            }
        } elseif (is_string($body)) {
            $this->setContentLength(strlen($body));
        }
        $this->options->put(GuzzleRequestOptions::BODY, $body);
        return $this;
    }

    protected function setContentLength(int $value): HttpRequestContract
    {
        if ($value > 0) {
            $this->headers()->set('Content-Length', $value);
        } else {
            $this->headers()->remove('Content-Length');
        }
        return $this;
    }

    protected function buildCookies()
    {
        $this->options->put(GuzzleRequestOptions::COOKIES, $this->cookieJar);
    }

    protected function buildHeaders()
    {
        $this->options->put(GuzzleRequestOptions::HEADERS, $this->headers()->all());
    }

    public function buildOptions(): array
    {
        if (0 !== count($files = $this->request->allFiles())) {
            $this->buildFilesRequest($files);
        } elseif ($this->request->isJson()) {
            $this->buildJsonBody($this->request->json()->all());
        } else {
            $this->buildRequestBody($this->request->getContent());
        }
        $this->buildCookies();
        $this->buildHeaders();
        return $this->options->toArray();
    }
}
