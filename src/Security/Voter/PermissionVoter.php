<?php

namespace Pharmaline\PhlAbc\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

#[AutoconfigureTag('phlabc.voter')]
class PermissionVoter implements VoterInterface
{
    public function __construct(
        private readonly Context $context
    ) {
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        try {
            if (!empty($this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) && !empty($attribute)) {
                return true;
            }
        } catch (AspectNotFoundException $e) {
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return int
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function voteOnAttribute(string $attribute, mixed $subject): int
    {
        $frontendUserPermissionAspect = $this->context->getAspect('frontend.user.permissions');
        $permissions = $frontendUserPermissionAspect->get('permissions');

        if (in_array($attribute, $permissions)) {
            return static::ACCESS_GRANTED;
        }

        return static::ACCESS_DENIED;
    }
}
