<?php

namespace Pharmaline\PhlAbc\Utility;

class PathUtility
{
    public function normalizePath(string $path, string $extension): string
    {
        $currentExt = pathinfo($path, PATHINFO_EXTENSION);
        if ($currentExt === '') {
            return $path . '.' . $extension;
        }
        $dir = dirname($path);
        $base = basename($path, '.' . $currentExt);
        return $dir . DIRECTORY_SEPARATOR . $base . '.' . $extension;
    }
}
