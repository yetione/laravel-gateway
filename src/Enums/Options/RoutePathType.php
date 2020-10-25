<?php


namespace Yetione\Gateway\Enums\Options;


use BenSampo\Enum\Enum;

final class RoutePathType extends Enum
{
    const FULL_PATH = 'full_path';

    const ROUTE_PATH = 'route_path';

    const PATHS = 'paths';

    const PATH_GATEWAY = 'gateway';

    const PATH_SERVICE = 'service';
}
