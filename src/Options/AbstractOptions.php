<?php


namespace Yetione\Gateway\Options;


use Yetione\Gateway\Exceptions\OptionNotSetException;
use Yetione\Gateway\Options\Contracts\OptionsContract;
use Yetione\Gateway\Options\Contracts\ResolverContract;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Yetione\Gateway\Options\Resolvers\ResolverConfigurator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class AbstractOptions implements OptionsContract
{
    protected Collection $options;

    protected ResolverContract $resolver;

    protected ResolverConfigurator $configurator;

    public function __construct(array $options, ResolverConfigurator $configurator)
    {
        $this->configurator = $configurator;
        $this->resolver = $this->configureResolver($this->createResolver($configurator));
        $this->setOptions($options);
    }

    public function createResolver(ResolverConfigurator $configurator): OptionsResolver
    {
        return new OptionsResolver($configurator);
    }

    abstract protected function configureResolver(OptionsResolver $resolver): ResolverContract;

    public function get(string $key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }

    public function getStrict(string $key)
    {
        Arr::has($this->options, $key);
        if (!Arr::has($this->options, $key)) {
            throw new OptionNotSetException(sprintf('Option [%s] is not set.', $key));
        }
        return $this->get($key);
    }

    public function all(): Collection
    {
        return $this->options;
    }

    public function setOptions(array $options): OptionsContract
    {
        $this->options = $this->getResolver()->process($options);
        return $this;
    }

    public function getResolver(): ResolverContract
    {
        return $this->resolver;
    }
}
