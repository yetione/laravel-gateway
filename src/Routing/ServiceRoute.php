<?php


namespace Yetione\Gateway\Routing;


use Yetione\Gateway\Exceptions\InvalidUri;
use Yetione\Gateway\Options\ServiceRouteOptions;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Yetione\Gateway\Routing\Contracts\ServiceRouteContract;
use Illuminate\Support\Facades\Log;

class ServiceRoute implements ServiceRouteContract
{
    protected ServiceContract $service;

    protected ServiceRouteOptions $options;

    protected string $id;

    protected string $routePath;

    public function __construct(ServiceContract $service, ServiceRouteOptions $options)
    {
        $this->options = $options;
        $this->service = $service;
    }

    public function id(): string
    {
        if (!isset($this->id)) {
            $this->id = $this->generateId();
        }
        return $this->id;
    }

    protected function generateId(): string
    {
        $routeHash = md5(implode('.', [
            implode(',', $this->method()),
            $this->path(),
            $this->public(),
            $this->alias()
        ]));
        return $this->service()->id().'.route.'.$routeHash;
    }

    public function service(): ServiceContract
    {
        return $this->service;
    }

    public function method(): array
    {
        return $this->options->get('method', $this->service()->method());
    }

    public function path(): string
    {
        return $this->options->get('path');
    }

    public function alias(): ?string
    {
        return $this->options->get('alias');
    }

    public function requestOptions(): array
    {
        return $this->options->get('request_options');
    }

    public function public(): bool
    {
        return $this->options->get('public', $this->service()->public());
    }

    public function middleware(): array
    {
        return $this->options->getStrict('middleware');
    }

    public function routePath(): string
    {
        if (!isset($this->routePath)) {
            $this->routePath = $this->buildRoutePath();
        }
        return $this->routePath;
    }

    public function serviceUri(array $parametersJar=[]): string
    {
        $host = $this->service()->resolveHost();
        $parsedHost = $this->service()->getHostResolver()->parseHost($host);
        $serviceUrl = $this->injectParams(
            $this->buildHost($parsedHost).$this->buildPath($parsedHost),
            $parametersJar
        );
        if (isset($parametersJar['query_string'])) {
            if (!isset($parsedHost['query'])) {
                $parsedHost['query'] = $parametersJar['query_string'];
            } else {
                $parsedHost['query'] .= '&'.$parametersJar['query_string'];
            }
        }
        if (isset($parsedHost['query'])) {
            $serviceUrl .= '?'.$parsedHost['query'];
        }
        if (isset($parsedHost['fragment'])) {
            $serviceUrl .= '#'.$parsedHost['fragment'];
        }
        Log::debug('ServiceURI', ['URI'=>$serviceUrl, 'parameters'=>$parametersJar, 'route'=>$this->toArray()]);
        return $serviceUrl;

    }

    protected function buildHost(array $parsedHost): string
    {
        return $this->service()->getHostResolver()->buildHost($parsedHost);
    }

    protected function buildPath(array $parsedHost): string
    {
        return $this->service()->getHostResolver()->buildPath($parsedHost, $this->cleanedPath());
    }

    protected function cleanedPath(): string
    {
        return $this->path();
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $prefix
     * @return string
     */
    protected function injectParams($url, array $params, $prefix = '')
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $url = $this->injectParams($url, $value, $prefix . $key . '.');
            }

            if (is_string($value) || is_numeric($value)) {
                $url = str_replace("{" . $prefix . $key . "}", $value, $url);
            }
        }

        return $url;
    }

    protected function buildRoutePath(): string
    {
        $path = !empty($alias = $this->alias()) ? $alias : $this->options->getStrict('path');
        return is_abs_path($path) ? $path : $this->service()->path().'/'.$path;
    }

    public function servicePath(): string
    {
        return $this->options->getStrict('service_path');
    }

    public function requestClass(): string
    {
        return $this->options->getStrict('request_class');
    }

    public function toArray(): array
    {
        return $this->options->all()->merge(['id'=>$this->id()])->toArray();
    }
}
