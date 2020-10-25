<?php


namespace Yetione\Gateway\Options;


use Yetione\Gateway\HttpClient\HttpRequest;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class ServiceRouteOptions extends AbstractOptions
{
    protected function configureResolver(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->withMethod();
        $resolver->withPath()->required();
        $resolver->withRequestOptions();
        $resolver->withPublic();
        $resolver->withPath('alias');
        $resolver->define('middleware')->required()->allowedTypes('array')->default([]);
        $resolver->define('request_class')->required()
            ->allowedTypes('string')->default(HttpRequest::class)
            ->allowedValues(static function (string $value): bool {
                return class_exists($value);
            });
        return $resolver;
    }
}
