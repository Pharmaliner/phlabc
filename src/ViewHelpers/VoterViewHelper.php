<?php

namespace Pharmaline\PhlAbc\ViewHelpers;

use Pharmaline\PhlAbc\Security\VoterInterface;
use Pharmaline\PhlAbc\Security\VoterServiceLocator;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
class VoterViewHelper extends AbstractConditionViewHelper
{
    public function __construct(
        VoterInterface $voter,
    )
    {
        VoterServiceLocator::setVoter($voter);
    }

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('permissionKey', 'string', 'Permission Key', true);
        $this->registerArgument('subject', 'mixed', 'Subject', false);
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $voter = VoterServiceLocator::getVoter();
        return $voter->vote($arguments['permissionKey'], $arguments['subject']);
    }
}