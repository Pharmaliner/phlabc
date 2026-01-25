<?php

namespace Pharmaline\PhlAbc\EventListener\Authorization;

use Pharmaline\PhlAbc\Session\PermissionSessionService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\Event\AfterUserLoggedInEvent;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

#[AsEventListener(identifier: 'pharmaline_phlabc_afterloggedin_save_permissions_in_session')]
class AfterLoggedInEventListener
{
    public function __construct(
        private readonly PermissionSessionService $permissionSessionService,
    ) {
    }

    /**
     * @param AfterUserLoggedInEvent $event
     */
    public function __invoke(AfterUserLoggedInEvent $event): void
    {
        $sessionUser = $event->getUser();
        if ($sessionUser instanceof FrontendUserAuthentication) {
            $this->permissionSessionService->setPermissions($sessionUser);
        }
    }
}
