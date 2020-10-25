<?php


namespace Yetione\Gateway\Options;


use Yetione\Gateway\Enums\HTTPRequestType;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Yetione\Gateway\Options\Resolvers\ResolverConfigurator;
use Symfony\Component\OptionsResolver\Options;

class ServiceOptions extends AbstractOptions
{
    protected GatewayOptions $gatewayOptions;

    public function __construct(array $options, ResolverConfigurator $configurator, GatewayOptions $gatewayOptions)
    {
        $this->gatewayOptions = $gatewayOptions;
        parent::__construct($options, $configurator);
    }

    protected function configureResolver(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->define('id')->required()->allowedTypes('string');

        $resolver->withMethod()->required()
            ->default([HTTPRequestType::GET, HTTPRequestType::POST, HTTPRequestType::PUT, HTTPRequestType::DELETE]);

        // По-умолчанию любой сервис публичный
        $resolver->withPublic()->default(true);

        // Путь может быть как относительным, так и абсолютным. По этому убираем только правый слэш.
        $resolver->withPath('path', ['trim_type'=>'right'])->required()
            ->default(static function (Options $options) {
                return $options['id'];
            });

        $resolver->withRequestOptions();

        $resolver->withHostname()->required();

        $defaultRoutePlaceholder = $this->gatewayOptions->getRoutePlaceholder();
        $resolver->define('default_route')->required()
            ->allowedTypes('array', 'bool')
            ->default([])
            ->allowedValues(static function ($value) {
                // Может быть либо массивом либо false
                return !is_array($value) ? false === $value : true;
            })
            ->normalize(static function (Options $options, $value) use ($defaultRoutePlaceholder){
                if (is_array($value)) {
                    $value = array_merge($value, [
                        'path'=> $defaultRoutePlaceholder,
                    ]);
                }
                return !$value ? false : $value;
            });

        return $resolver;
    }
}
