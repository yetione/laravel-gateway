<?php


namespace Yetione\Gateway\Options\Traits;


use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

/**
 * Trait RequestOptions
 * @package Yetione\Gateway\Options\Traits
 * @see http://docs.guzzlephp.org/en/stable/request-options.html
 */
trait RequestOptions
{
    protected function configureRequestOptionsResolver(SymfonyOptionsResolver $resolver): SymfonyOptionsResolver
    {
        $resolver->define(GuzzleRequestOptions::ALLOW_REDIRECTS)
            ->allowedTypes('bool')->default(false);
        $resolver->define(GuzzleRequestOptions::AUTH)
            ->allowedTypes('string', 'string[]', 'null')->default(null);
        $resolver->define(GuzzleRequestOptions::BODY)
            ->allowedTypes('string', 'resource', StreamInterface::class);
        $resolver->define(GuzzleRequestOptions::CERT)
            ->allowedTypes('string', 'string[]');
        $resolver->define(GuzzleRequestOptions::COOKIES)
            ->allowedTypes(CookieJarInterface::class);
        $resolver->define(GuzzleRequestOptions::CONNECT_TIMEOUT)
            ->allowedTypes('float', 'int')->default(0)
            ->normalize(static function (Options $options, $value) {
                return (float) $value;
            });
        $resolver->define(GuzzleRequestOptions::DEBUG)
            ->allowedTypes('bool', 'resource');
        $resolver->define(GuzzleRequestOptions::DECODE_CONTENT)
            ->allowedTypes('bool', 'string')->default(true);
        $resolver->define(GuzzleRequestOptions::DELAY)
            ->allowedTypes('int', 'float');
        $resolver->define(GuzzleRequestOptions::EXPECT)
            ->allowedTypes('bool', 'integer');
        $resolver->define(GuzzleRequestOptions::FORCE_IP_RESOLVE)
            ->allowedTypes('string');
        $resolver->define(GuzzleRequestOptions::FORM_PARAMS)
            ->allowedTypes('array');
        $resolver->define(GuzzleRequestOptions::HEADERS)
            ->allowedTypes('array');
        $resolver->define(GuzzleRequestOptions::HTTP_ERRORS)
            ->allowedTypes('bool')->default(true);
        $resolver->define(GuzzleRequestOptions::IDN_CONVERSION)
            ->allowedTypes('bool', 'int');
        $resolver->define(GuzzleRequestOptions::JSON);
        $resolver->define(GuzzleRequestOptions::MULTIPART)
            ->allowedTypes('array');
        $resolver->define(GuzzleRequestOptions::ON_HEADERS)
            ->allowedTypes('callable');
        $resolver->define(GuzzleRequestOptions::ON_STATS)
            ->allowedTypes('callable');
        $resolver->define(GuzzleRequestOptions::PROGRESS)
            ->allowedTypes('callable');
        $resolver->define(GuzzleRequestOptions::PROXY)
            ->allowedTypes('string', 'string[]');
        $resolver->define(GuzzleRequestOptions::QUERY)
            ->allowedTypes('string', 'array');
        $resolver->define(GuzzleRequestOptions::READ_TIMEOUT)
            ->allowedTypes('float', 'int')
            ->default((float) ini_get('default_socket_timeout'))
            ->normalize(static function (Options $options, $value) {
                return (float) $value;
            });
        $resolver->define(GuzzleRequestOptions::SINK)
            ->allowedTypes('string', 'resource', StreamInterface::class);
        $resolver->define(GuzzleRequestOptions::SSL_KEY)
            ->allowedTypes('string', 'string[]');
        $resolver->define(GuzzleRequestOptions::STREAM)
            ->allowedTypes('bool')->default(false);
        $resolver->define(GuzzleRequestOptions::SYNCHRONOUS)
            ->allowedTypes('bool');
        $resolver->define(GuzzleRequestOptions::VERIFY)
            ->allowedTypes('bool', 'string')->default(true);
        $resolver->define(GuzzleRequestOptions::TIMEOUT)
            ->allowedTypes('float', 'int')->default(0)
            ->normalize(static function (Options $options, $value) {
                return (float) $value;
            });
        $resolver->define(GuzzleRequestOptions::VERSION)
            ->allowedTypes('float', 'string')->default(1.1);
        return $resolver;
    }
}
