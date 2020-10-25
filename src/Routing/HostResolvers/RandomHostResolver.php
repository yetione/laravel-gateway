<?php


namespace Yetione\Gateway\Routing\HostResolvers;


class RandomHostResolver extends AbstractHostResolver
{

    /**
     * @param array $host
     * @return string
     */
    public function resolve($host): string
    {
        if (!is_array($host)) {
            throw new \InvalidArgumentException(sprintf('Host must be an array, [%s] given.', gettype($host)));
        }
        return $host[array_rand($host, 1)];
    }
}
