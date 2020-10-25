<?php


namespace Yetione\Gateway\Enums;


use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

final class HTTPRequestScheme extends Enum
{
    const HTTP = 'http';
    const HTTPS = 'https';

    public static function schemeFull(string $scheme): ?string
    {
        try {
            $val = self::fromValue($scheme);
            return $val->value.'://';
        } catch (InvalidEnumMemberException $e) {
            return null;
        }
    }
}
