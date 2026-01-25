<?php

namespace Pharmaline\PhlAbc\ViewHelpers;

use Pharmaline\PhlAbc\Security\SecurityManager;
use Pharmaline\PhlAbc\Utility\StaticServiceLocatorUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class VoterViewHelper extends AbstractConditionViewHelper
{
    public function __construct(
        SecurityManager $securityManager,
    ) {
        StaticServiceLocatorUtility::setService($securityManager);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('permissionKey', 'string', 'Voter Class', true);
        $this->registerArgument('subject', 'mixed', 'Subject to check permission on (e.g., Product, Offer)', false, null);
        $this->registerArgument('strategy', 'string', 'Voting strategy: unanimous, affirmative', false, 'unanimous', null);
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $security = StaticServiceLocatorUtility::getService();

        return $security->vote(
            $arguments['permissionKey'],
            $arguments['subject'] ?? null,
            $arguments['strategy'] ?? null
        );
    }
}
