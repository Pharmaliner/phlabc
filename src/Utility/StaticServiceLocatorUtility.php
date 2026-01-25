<?php

namespace Pharmaline\PhlAbc\Utility;

use RuntimeException;

class StaticServiceLocatorUtility
{
    private static mixed $service = null;

    public static function setService(mixed $service): void
    {
        self::$service = $service;
    }

    public static function getService(): mixed
    {
        if (!self::$service) {
            throw new RuntimeException('Service not set in ServiceLocator.');
        }

        return self::$service;
    }
}
