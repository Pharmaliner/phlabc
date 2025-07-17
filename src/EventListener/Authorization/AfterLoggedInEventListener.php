<?php

namespace Pharmaline\PhlAbc\EventListener\Authorization;

use Pharmaline\PhlAbc\Session\PermissionSessionService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\Event\AfterUserLoggedInEvent;

#[AsEventListener(identifier: 'pharmaline_phlabc_afterloggedin_save_permissions_in_session')]
class AfterLoggedInEventListener
{
    public function __construct(
        private readonly PermissionSessionService $permissionSessionService,
    )
    {
    }

    /**
     * @param AfterUserLoggedInEvent $event
     * @return void
     */
    public function __invoke(AfterUserLoggedInEvent $event): void
    {
        $sessionUser = $event->getUser();
        $this->permissionSessionService->setPermissions($sessionUser);
    }
}