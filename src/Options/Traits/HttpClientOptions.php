<?php


namespace Yetione\Gateway\Options\Traits;

use Psr\Http\Message\UriInterface;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;
use Yetione\Gateway\Enums\HttpClientOptions as HttpClientOptionsEnum;

trait HttpClientOptions
{
    protected function configureHttpClientOptionsResolver(SymfonyOptionsResolver $resolver): SymfonyOptionsResolver
    {
        $resolver->define(HttpClientOptionsEnum::BASE_URI)->allowedTypes('string', UriInterface::class);
//        $resolver->define(HttpClientOptionsEnum::HANDLER)->allowedTypes('callable');
        return $resolver;
    }
}
