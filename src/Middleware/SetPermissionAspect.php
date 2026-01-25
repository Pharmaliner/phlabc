<?php

namespace Pharmaline\PhlAbc\Middleware;

use Pharmaline\PhlAbc\Context\PermissionAspect;
use Pharmaline\PhlAbc\Domain\Repository\FrontendUserRepository;
use Pharmaline\PhlAbc\Utility\PermissionUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class SetPermissionAspect implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = GeneralUtility::makeInstance(Context::class);
        /**
         * @var FrontendUserAuthentication $user
         */
        $user = $request->getAttribute('frontend.user');

        if ($user->getUserId() !== null) {
            $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

            $frontendUser = $frontendUserRepository->findByUid($user->getUserId());

            $permissions = PermissionUtility::extractPermissions($frontendUser);
            $permissionsDeny = PermissionUtility::extractPermissionsDeny($frontendUser);

            $permissions = array_diff($permissions, $permissionsDeny);

        }

        $context->setAspect(
            'frontend.user.permissions',
            GeneralUtility::makeInstance(PermissionAspect::class, $request, $permissions ?? [])
        );

        return $handler->handle($request);
    }
}
