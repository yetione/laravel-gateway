<?php


namespace Yetione\Gateway\Routing\Contracts;


use Yetione\Gateway\Exceptions\InvalidUri;
use InvalidArgumentException;

interface HostResolverContract
{
    /**
     * @param array|string $host
     * @return string
     * @throws InvalidArgumentException
     */
    public function resolve($host): string;

    /**
     * @param string $host
     * @return array
     * @throws InvalidUri
     */
    public function parseHost(string $host): array;

    public function buildHost(array $parsedHost): string;

    public function buildPath(array $parsedHost, string $path): string;
}
