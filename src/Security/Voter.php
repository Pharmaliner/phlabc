<?php

namespace Pharmaline\PhlAbc\Security;

use Pharmaline\PhlAbc\Domain\Repository\FrontendGroupRepository;
use Pharmaline\PhlAbc\Domain\Repository\FrontendUserRepository;
use Pharmaline\PhlAbc\Exception\MissingOwnerAttributeInObjectException;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Voter implements VoterInterface
{
    protected FrontendUserAuthentication $frontendUserAuthentication;

    /**
     * @param string $permission
     * @param mixed|null $subject
     * @return bool
     * @throws MissingOwnerAttributeInObjectException
     */
    public function vote(string $permission, mixed $subject = null): bool
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        $this->frontendUserAuthentication = $request->getAttribute('frontend.user');

        // @TODO configure user ownership check is enable than do following checks
        if (is_object($subject)) {
            // When we send an object as the subject, we check here whether access to the object is legitimate.
            return $this->hasAccessToObject($permission, $subject);
        }

        return $this->hasAccess($permission);
    }
    
    /**
     * @param string $permission
     * @param object $object
     * @return bool
     * @throws MissingOwnerAttributeInObjectException
     */
    private function hasAccessToObject(string $permission, object $object): bool
    {
        if (property_exists($object, 'cruser_id') === false &&
            method_exists($object, 'getCrUserId') === false) {
            throw new MissingOwnerAttributeInObjectException(
                'Missing attribute cruser_id or getter method getCrUserId for object: ' . get_class($object)
            );
        }

        $userId = $this->frontendUserAuthentication->getUserId();
        if ($userId === $object->getCrUserId() || $this->isGranted($permission)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $permission
     * @return bool
     */
    private function hasAccess(string $permission): bool
    {
        return $this->isGranted($permission);
    }

    private function isGranted(string $permission): bool
    {
        $permissions = $this->frontendUserAuthentication->getSessionData('permissions');
        return in_array($permission, $permissions);
    }
}