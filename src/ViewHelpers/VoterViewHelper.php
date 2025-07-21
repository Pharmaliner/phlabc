<?php

namespace Pharmaline\PhlAbc\ViewHelpers;

use Pharmaline\PhlAbc\Security\VoterInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
class VoterViewHelper extends AbstractConditionViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('permission_key', 'bool', 'Permission Key', true);
        $this->registerArgument('subject', 'mixed', 'Subject', false);
    }

    /**
     * @param array<mixed> $arguments
     * @return bool
     */
    protected static function evaluateCondition(array $arguments = []): bool
    {
        $voter = GeneralUtility::makeInstance(VoterInterface::class);
        return $voter->vote($arguments['permission_key'], $arguments['subject']);
    }
}