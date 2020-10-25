<?php


namespace Yetione\Gateway\Options;



use Yetione\Gateway\Enums\HTTPRequestScheme;
use Yetione\Gateway\Options\Resolvers\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class GatewayOptions extends AbstractOptions
{
    protected string $routePlaceholder;

    protected function configureResolver(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->withPath('path', [
            'trim_type'=>'both',
            'add_left_slash'=>true,
        ])->required()->default('/');
        $resolver->withPath('route_path', ['trim_type'=>'both'])
            ->required()->allowedTypes('string')
            ->default(function (Options $options) {
                return $options['path'];
            });
        $resolver->define('allowed_schemes')->allowedTypes('string[]')->required()->default([HTTPRequestScheme::HTTP, HTTPRequestScheme::HTTPS]);
        $resolver->define('default_scheme')->allowedTypes('string')->required()->default(HTTPRequestScheme::HTTP);
        $resolver->define('domain')->required()->allowedTypes('string');

        $resolver->define('http_client_options')->allowedTypes('array')->required()->default([]);
        $resolver->define('request_options')->allowedTypes('array')->required()->default([]);

        $resolver->define('route_param_name')->required()->default('url')->allowedTypes('string');
        $resolver->define('route_param_regexp')->required()->default('url')->allowedTypes('string');
        $resolver->define('route_param_with_regexp')->required()->default(true)->allowedTypes('bool');
        $resolver->define('route_param_optional')->required()->default(true)->allowedTypes('bool');

        return $resolver;
    }

    public function routePath(): string
    {
        return $this->get('route_path');
    }

    public function path(): string
    {
        return $this->get('path');
    }

    public function defaultScheme(): string
    {
        return $this->get('default_scheme');
    }

    public function allowedSchemes(): array
    {
        return $this->get('allowed_schemes');
    }

    public function getRoutePlaceholder(): string
    {
        if (!isset($this->routePlaceholder)) {
            $result = $this->get('route_param_name');
            if ($this->get('route_param_with_regexp')) {
                $result .= ':'.$this->get('route_param_regexp');
            }
            $result = "/{{$result}}";
            if ($this->get('route_param_optional')) {
                $result = "[{$result}]";
            }
            $this->routePlaceholder = $result;
        }
        return $this->routePlaceholder;
    }

    public function getServicePathRoutePlaceholder()
    {
        return "{{$this->get('route_param_name')}}";
    }
}
