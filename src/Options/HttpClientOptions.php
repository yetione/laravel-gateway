<?php


namespace Yetione\Gateway\Options;


use Yetione\Gateway\Options\Contracts\ResolverContract;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Yetione\Gateway\Options\Traits\RequestOptions;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;
use Yetione\Gateway\Options\Traits\HttpClientOptions as HttpClientOptionsTrait;

/**
 * Class HttpClientOptions
 * @package Yetione\Gateway\Options
 */
class HttpClientOptions extends AbstractOptions
{
    use RequestOptions, HttpClientOptionsTrait;


    protected function configureResolver(OptionsResolver $resolver): ResolverContract
    {
        $resolver->define('client_options')->required()
            ->default(function (SymfonyOptionsResolver $optionsResolver, Options $options) {
                $this->configureHttpClientOptionsResolver($optionsResolver);
            });
        $resolver->define('request_options')->required()
            ->default(function (SymfonyOptionsResolver $optionsResolver, Options $options) {
                $this->configureRequestOptionsResolver($optionsResolver);
            });
        $resolver->define('hop_by_hop_headers')->required()
            ->allowedTypes('string[]')->default([]);
        $resolver->define('skipped_headers')->required()
            ->allowedTypes('string[]')->default([]);
        return $resolver;
    }
}
