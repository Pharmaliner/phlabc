<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\UserFuncs\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA ctrl 'label_userFunc' for permissions/roles tables.
 *
 * Renders the record title using the 'title_translation_key' (an LLL: reference) resolved in
 * the current backend user's language, falling back to the stored default-language 'title'
 * with an optional key column appended.
 *
 * Usage in TCA:
 *   'ctrl' => [
 *       'label'          => 'title',
 *       'label_userFunc' => \Pharmaline\PhlAbc\UserFuncs\TCA\LabelUserFunc::class . '->getLabel',
 *       'label_userFunc_options' => [
 *          'keyField' => 'role_key', // (or 'permission_key' for the permissions table)
 *       ],
 *   ],
 */
final class LabelUserFunc
{
    public function getLabel(array &$parameters): void
    {
        $table = (string)($parameters['table'] ?? '');
        if ($table === '') {
            return;
        }

        $row = $parameters['row'] ?? [];
        $rawUid = (string)($row['uid'] ?? '');

        $keyField = (string)($parameters['options']['keyField'] ?? '');
        if ($keyField !== '' && !isset($GLOBALS['TCA'][$table]['columns'][$keyField])) {
            $keyField = '';
        }

        if ($rawUid !== '' && !str_starts_with($rawUid, 'NEW') && (int)$rawUid > 0) {
            $selectFields = 'title_translation_key, title' . ($keyField !== '' ? ', ' . $keyField : '');
            $row = BackendUtility::getRecord($table, (int)$rawUid, $selectFields) ?? [];
        }

        $title = (string)($row['title'] ?? '');
        $keyValue = $keyField !== '' ? (string)($row[$keyField] ?? '') : '';

        if (!empty($row['title_translation_key'])) {
            $languageService = $this->getLanguageService();
            if ($languageService !== null) {
                $translationKey = (string)$row['title_translation_key'];
                $translatedTitle = $languageService->sL($translationKey);
                // sL() might return the translation_key if it can't be translated.
                if ($translatedTitle !== '' && $translatedTitle !== $translationKey) {
                    $title = $translatedTitle;
                }
            }
        }

        if ($title !== '') {
            $parameters['title'] = $title;

            if ($keyValue !== '') {
                $parameters['title'] .= " ($keyValue)";
            }
        }
    }

    private function getLanguageService(): ?LanguageService
    {
        if (($GLOBALS['LANG'] ?? null) instanceof LanguageService) {
            return $GLOBALS['LANG'];
        }

        $beUser = $GLOBALS['BE_USER'] ?? null;
        if ($beUser === null) {
            return null;
        }

        return GeneralUtility::makeInstance(LanguageServiceFactory::class)
            ->createFromUserPreferences($beUser);
    }
}
