<?php

namespace Pharmaline\PhlAbc\Utility;

class StorageConfigurationUtility
{
    public static function getPermissionStoragePid(): int
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['phlabc']['storage']['permission'] ?? 1;
    }

    public static function getRoleStoragePid(): int
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['phlabc']['storage']['role'] ?? 1;
    }
}
