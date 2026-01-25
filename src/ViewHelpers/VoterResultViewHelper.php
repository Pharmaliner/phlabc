<?php

namespace Pharmaline\PhlAbc\ViewHelpers;

use Pharmaline\PhlAbc\Security\SecurityManager;
use Pharmaline\PhlAbc\Utility\StaticServiceLocatorUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class VoterResultViewHelper extends AbstractViewHelper
{
    public function __construct(
        SecurityManager $securityManager,
    ) {
        StaticServiceLocatorUtility::setService($securityManager);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('permissionKey', 'string', 'Permission Key', true);
        $this->registerArgument(
            'subject',
            'mixed',
            'Subject to check permission on (e.g., Product, Offer)',
            false,
            null
        );
        $this->registerArgument(
            'strategy',
            'string',
            'Voting strategy: unanimous, affirmative',
            false,
            'unanimous',
            null
        );
    }

    /**
     * @return bool
     */
    public function render(): bool
    {
        $security = StaticServiceLocatorUtility::getService();

        return $security->vote(
            $this->arguments['permissionKey'],
            $this->arguments['subject'] ?? null,
            $this->arguments['strategy'] ?? null
        );
    }
}
