<?php


namespace Yetione\Gateway\Options;


use Yetione\Gateway\Options\Contracts\ResolverContract;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Yetione\Gateway\Options\Resolvers\ResolverConfigurator;
use Yetione\Gateway\Routing\Contracts\HostResolverContract;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class PassportServiceOptions extends AbstractOptions
{
    protected HostResolverContract $hostResolver;


    public function __construct(array $options, ResolverConfigurator $configurator, HostResolverContract $hostResolver)
    {
        parent::__construct($options, $configurator);
        $this->hostResolver = $hostResolver;
    }

    protected function configureResolver(OptionsResolver $resolver): ResolverContract
    {
        $resolver->withHostname()->required();
        $resolver->define('endpoints')->required()
            ->default(function (SymfonyOptionsResolver $optionsResolver, Options $options) {
                $optionsResolver->define('retrieveByHeader')->required()
                    ->default(function (SymfonyOptionsResolver $optionsResolver, Options $options){
                        $this->defineEndpoint($optionsResolver, $options);
                    });
            });
        return $resolver;
    }

    /**
     * @param OptionsResolver|SymfonyOptionsResolver $optionsResolver
     * @param Options $options
     * @return SymfonyOptionsResolver
     */
    protected function defineEndpoint(SymfonyOptionsResolver $optionsResolver, Options $options): SymfonyOptionsResolver
    {
        $this->configurator->withMethod($optionsResolver)->required();
        $this->configurator->withPath($optionsResolver)->required();
        $this->configurator->withRequestOptions($optionsResolver);
        return $optionsResolver;
    }

    public function hostname(): string
    {
        return $this->hostResolver->resolve($this->getStrict('hostname'));
    }

    public function endpointUri(string $endpointName): ?string
    {
        if (null !== ($endpoint = $this->get('endpoints.'.$endpointName))) {
            $parsedHost = $this->hostResolver->parseHost($this->hostname());
            return $this->hostResolver->buildHost($parsedHost).
                $this->hostResolver->buildPath($parsedHost, $endpoint['path']);
        }
        return null;
    }



}
