<?php

namespace Pharmaline\PhlAbc\Service;

use Composer\InstalledVersions;

class ComposerService
{
    /**
     * @return array<string, array{roles: string, permissions: string, presets: string}>
     */
    public function getYamlFilesOfExtension(): array
    {
        $paths = [];

        $composerPackages = InstalledVersions::getInstalledPackages();
        foreach ($composerPackages as $package) {
            $installPath = InstalledVersions::getInstallPath($package);
            if (file_exists($installPath . DIRECTORY_SEPARATOR . 'composer.json')) {
                $contentOfComposer = file_get_contents($installPath . DIRECTORY_SEPARATOR . 'composer.json');

                $composer = json_decode($contentOfComposer, true);

                if (array_key_exists('extra', $composer) &&
                    array_key_exists('pharmaline/abc', $composer['extra'])
                ) {
                    $paths[$package]['roles'] = $installPath . DIRECTORY_SEPARATOR . $composer['extra']['pharmaline/abc']['roles'];
                    $paths[$package]['permissions'] = $installPath . DIRECTORY_SEPARATOR . $composer['extra']['pharmaline/abc']['permissions'];
                    $paths[$package]['presets'] = $installPath . DIRECTORY_SEPARATOR . $composer['extra']['pharmaline/abc']['presets'];
                }
            }
        }

        return $paths;
    }
}