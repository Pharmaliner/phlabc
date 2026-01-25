<?php

namespace Pharmaline\PhlAbc\Service;

use Exception;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;

class YamlService
{
    private YamlFileLoader $yamlLoader;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        $this->yamlLoader = new YamlFileLoader($this->logger);
    }

    /**
     * @param string $fileName
     * @return array
     * @throws Exception
     */
    public function loadFile(string $fileName): array
    {
        if (file_exists($fileName) === false) {
            throw new Exception('File does not exist: ' . $fileName);
        }

        if (filesize($fileName) <= 0) {
            throw new Exception('File is empty: ' . $fileName);
        }

        $link = realpath($fileName);

        return $this->yamlLoader->load($link, YamlFileLoader::PROCESS_PLACEHOLDERS | YamlFileLoader::PROCESS_IMPORTS);
    }
}
