<?php


namespace Yetione\Gateway\Routing;


class DefaultServiceRoute extends ServiceRoute
{
    protected function buildRoutePath(): string
    {
        return $this->service()->path().$this->options->getStrict('path');
    }

    protected function buildPath(array $parsedHost): string
    {
        return $this->cleanedPath();
    }

    protected function cleanedPath(): string
    {
        return preg_replace('/\[?\/?\{(\w+)(:.*)?\}\]?/', '{${1}}', parent::cleanedPath());
    }
}
