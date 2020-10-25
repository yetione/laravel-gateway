<?php


namespace Yetione\Gateway\Routing\HostResolvers;


use Yetione\Gateway\Exceptions\InvalidUri;
use Yetione\Gateway\Options\GatewayOptions;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;

abstract class AbstractHostResolver implements HostResolverContract
{
    protected GatewayOptions $gatewayOptions;

    public function __construct(GatewayOptions $gatewayOptions)
    {
        $this->gatewayOptions = $gatewayOptions;
    }

    public function parseHost(string $host): array
    {
        /** @var array $parsedHost */
        if (false === ($parsedHost = parse_url($host))) {
            throw new InvalidUri(sprintf('Hostname [%s] is invalid. Parsing finished with error.', $host));
        }
        if (!isset($parsedHost['host'])) {
            throw new InvalidUri(sprintf('Hostname [%s] is invalid. Host part of URI is not set.', $host));
        }
        if (!isset($parsedHost['scheme'])) {
            $parsedHost['scheme'] = $this->gatewayOptions->defaultScheme();
        }
        if (!in_array($parsedHost['scheme'], $this->gatewayOptions->allowedSchemes())) {
            throw new InvalidUri(
                sprintf('Hostname [%s] is invalid. Scheme [%s] is not supported.', $host, $parsedHost['scheme'])
            );
        }
        return $parsedHost;
    }

    public function buildHost(array $parsedHost): string
    {
        $result = "{$parsedHost['scheme']}://";
        if (isset($parsedHost['user']) || isset($parsedHost['pass'])) {
            $result .= "{$parsedHost['user']}:{$parsedHost['pass']}@";
        }
        $result .= $parsedHost['host'];
        if (isset($parsedHost['port'])) {
            $result .= ":{$parsedHost['port']}";
        }
        return $result;
    }

    public function buildPath(array $parsedHost, string $path): string
    {
        if (is_abs_path($path)) {
            $parsedHost['path'] = $path;
        } else {
            if (!isset($parsedHost['path'])) {
                $parsedHost['path'] = '';
            }
            $parsedHost['path'] .= '/'.$path; // Тут добавляем слэш, тк его нету
        }
        return $parsedHost['path'];
    }
}
