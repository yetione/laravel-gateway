<?php


namespace Yetione\Gateway\Options\Resolvers;

use Yetione\Gateway\Options\Contracts\ResolverContract;
use Illuminate\Support\Collection;
use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class OptionsResolver extends SymfonyOptionsResolver implements ResolverContract
{

    protected ResolverConfigurator $configurator;

    public function __construct(ResolverConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }

    public function withMethod(string $option='method'): OptionConfigurator
    {
        return $this->configurator->withMethod($this, $option);
    }

    public function withRequestOptions(string $option='request_options'): OptionConfigurator
    {
        return $this->configurator->withRequestOptions($this, $option);
    }

    public function withPath(string $option='path', array $processOptions=[]): OptionConfigurator
    {
        return $this->configurator->withPath($this, $option, $processOptions);
    }

    public function withPublic(string $option='public'): OptionConfigurator
    {
        return $this->configurator->withPublic($this, $option);
    }

    public function withHostname(string $option='hostname'): OptionConfigurator
    {
        return $this->configurator->withHostname($this, $option);
    }

    public function process(array $options = []): Collection
    {
        return collect($this->resolve($options));
    }
}
