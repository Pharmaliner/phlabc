<?php

namespace Pharmaline\PhlAbc\Context;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class PermissionAspect implements AspectInterface
{
    private readonly array $permissions;

    public function __construct(ServerRequestInterface $request, array $givenPermissions = [])
    {
        $permissions = [];

        $user = $request->getAttribute('frontend.user');
        if ($user instanceof FrontendUserAuthentication) {
            if ($user->getSessionData('permissions') !== null) {
                $sessionPermissions = $user->getSessionData('permissions');
                $permissions = is_array($sessionPermissions) ? $sessionPermissions : [];
            } else {
                $user->setSessionData('permissions', $givenPermissions);
                $permissions = $givenPermissions;
            }
        }

        $this->permissions = $permissions;
    }

    /**
     * @param string $name
     * @return array<int, string>
     */
    public function get(string $name): array
    {
        return match ($name) {
            default => $this->getPermissions(),
        };
    }

    /**
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
