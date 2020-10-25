<?php


namespace Yetione\Gateway\Enums;


use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

final class HTTPRequestType extends Enum
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const COPY = 'COPY';
    const HEAD = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const LINK = 'LINK';
    const UNLINK = 'UNLINK';
    const PURGE = 'PURGE';
    const LOCK = 'LOCK';
    const UNLOCK = 'UNLOCK';
    const PROPFIND = 'PROPFIND';
    const VIEW = 'VIEW';

    public static function isValid(string $method): bool
    {
        try {
            HTTPRequestType::fromValue(mb_strtoupper($method));
            return true;
        } catch (InvalidEnumMemberException $e) {
            return false;
        }
    }
}
