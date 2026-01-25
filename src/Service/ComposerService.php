<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Service;

use Composer\InstalledVersions;

/**
 * @phpstan-type ComposerJson array{extra?: array{phl-abc?: array{roles?: string, permissions?: string, presets?: string}}}
 * @phpstan-type YamlPaths array{roles?: string, permissions?: string, presets?: string}
 */
class ComposerService
{
    private const PHARMALINE_ABC_KEY = 'phl-abc';
    private const COMPOSER_JSON_FILE = 'composer.json';

    /** @var array<int, string> */
    private const YAML_TYPES = ['roles', 'permissions', 'presets'];

    /**
     * @return array<string, YamlPaths>
     */
    public function getYamlFilesOfExtension(): array
    {
        $paths = [];
        $composerPackages = InstalledVersions::getInstalledPackages();

        foreach ($composerPackages as $package) {
            $yamlPaths = $this->extractYamlPathsFromPackage($package);

            if ($yamlPaths !== []) {
                $paths[$package] = $yamlPaths;
            }
        }

        return $paths;
    }

    /**
     * @param string $package
     * @return YamlPaths
     */
    private function extractYamlPathsFromPackage(string $package): array
    {
        $installPath = InstalledVersions::getInstallPath($package);

        if ($installPath === null) {
            return [];
        }

        $composerData = $this->readComposerJson($installPath);

        if ($composerData === null) {
            return [];
        }

        return $this->buildYamlPaths($installPath, $composerData);
    }

    /**
     * @param string $installPath
     * @return ComposerJson|null
     */
    private function readComposerJson(string $installPath): ?array
    {
        $composerJsonPath = $installPath . DIRECTORY_SEPARATOR . self::COMPOSER_JSON_FILE;

        if (!file_exists($composerJsonPath)) {
            return null;
        }

        $content = file_get_contents($composerJsonPath);

        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param string $installPath
     * @param ComposerJson $composerData
     * @return YamlPaths
     */
    private function buildYamlPaths(string $installPath, array $composerData): array
    {
        $pharmalineConfig = $this->getPharmalineConfig($composerData);

        if ($pharmalineConfig === null) {
            return [];
        }

        $paths = [];

        foreach (self::YAML_TYPES as $type) {
            if (isset($pharmalineConfig[$type])) {
                $relativePath = $pharmalineConfig[$type];
                if (is_string($relativePath)) {
                    $paths[$type] = $installPath . DIRECTORY_SEPARATOR . $relativePath;
                }
            }
        }

        return $paths;
    }

    /**
     * @param ComposerJson $composerData
     * @return array{roles?: string, permissions?: string, presets?: string}|null
     */
    private function getPharmalineConfig(array $composerData): ?array
    {
        if (!isset($composerData['extra'])) {
            return null;
        }

        if (!is_array($composerData['extra'])) {
            return null;
        }

        if (!isset($composerData['extra'][self::PHARMALINE_ABC_KEY])) {
            return null;
        }

        $pharmalineConfig = $composerData['extra'][self::PHARMALINE_ABC_KEY];

        if (!is_array($pharmalineConfig)) {
            return null;
        }

        return $pharmalineConfig;
    }
}