<?php


namespace Yetione\Gateway\Options\Resolvers;


use Yetione\Gateway\Enums\HTTPRequestType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class ResolverConfigurator
{
    public const TRIM_BOTH = 'both';
    public const TRIM_LEFT = 'left';
    public const TRIM_RIGHT = 'right';
    public const TRIM_NONE = 'none';

    protected const TRIM_FUNCTIONS = [
        self::TRIM_BOTH => 'trim',
        self::TRIM_LEFT => 'left',
        self::TRIM_RIGHT => 'rtrim',
        self::TRIM_NONE => '',
    ];

    public static function processPath(string $value, array $options=[]): string
    {
        $trimFunction = self::TRIM_FUNCTIONS[$options['trim_type'] ?? 'none'] ?? null;
        if (null === $trimFunction) {
            throw new InvalidArgumentException(sprintf('Invalid trim type [%s].', $options['trim_type']));
        } else {
            $result = function_exists($trimFunction) ?
                call_user_func_array($trimFunction, [$value, $options['trim_chars'] ?? '/']) :
                $value;
        }
        $addLeftSlash = (bool) ($options['add_left_slash'] ?? false);
        $addRightSlash = (bool) ($options['add_right_slash'] ?? false);
        if ($addLeftSlash || $addRightSlash) {
            $slash = $options['slash'] ?? '/';
            $len = mb_strlen($result);
            $firstChar = $len > 0 ? $result[0] : null;
            $lastChar = $len > 0 ? $result[$len - 1] : null;
            if ($addLeftSlash && $firstChar !== $slash) {
                $result = $slash.$result;
            }
            if (0 < $len && $addRightSlash && $lastChar !== $slash) {
                $result .= $slash;
            }
        }
        return $result;
    }

    public function withMethod(SymfonyOptionsResolver $resolver, string $option='method'): OptionConfigurator
    {
        return $resolver->define($option)
            ->allowedTypes('string', 'string[]')
            ->allowedValues(
                function ($value) {
                    $result = true;
                    collect($value)->each(static function ($item, $key) use (&$result) {
                        try {
                            return HTTPRequestType::fromValue(mb_strtoupper($item));
                        } catch (InvalidEnumMemberException $e) {
                            $result = false;
                            return false;
                        }
                    });
                    return $result;
                })->normalize(static function (Options $options, $value) {
                return (array) $value;
            });
    }

    public function withRequestOptions(SymfonyOptionsResolver $resolver, string $option='request_options'): OptionConfigurator
    {
        return $resolver->define($option)->allowedTypes('array')->required()->default([]);
    }

    public function withPath(SymfonyOptionsResolver $resolver, string $option='path', array $processOptions=[]): OptionConfigurator
    {
        return $resolver->define($option)
            ->allowedTypes('string')
            ->normalize(static function (SymfonyOptionsResolver $resolver, $value) use ($processOptions) {
                return ResolverConfigurator::processPath($value, $processOptions);
            });
    }

    public function withPublic(SymfonyOptionsResolver $resolver, string $option='public'): OptionConfigurator
    {
        return $resolver->define($option)->allowedTypes('bool');
    }

    public function withHostname(SymfonyOptionsResolver $resolver, string $option='hostname'): OptionConfigurator
    {
        return $resolver->define($option)
            ->allowedTypes('string', 'string[]')
            ->normalize(static function(Options $options, $value) {
                // Убираем правый слэш от имени хоста, что бы нормально склеивать абсолютные и относительные ссылки
                return array_map(static function(string $hostname): string {
                    return ResolverConfigurator::processPath($hostname, ['trim_type'=>'right']);
                }, (array) $value);
            });
    }
}
