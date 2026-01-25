<?php

namespace Pharmaline\PhlAbc\Security;

use Pharmaline\PhlAbc\Security\Service\VoterRegistry;
use Pharmaline\PhlAbc\Security\Strategy\AccessDecisionStrategyFactory;
use Pharmaline\PhlAbc\Security\Strategy\AccessDecisionStrategyInterface;
use Pharmaline\PhlAbc\Security\Voter\VoterInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;

class SecurityManager
{
    /** @var VoterInterface[] */
    private array $voters;

    /**
     * @param VoterRegistry $registry
     * @param Context $context
     * @param LoggerInterface $logger
     * @param AccessDecisionStrategyFactory $strategyFactory
     * @param AccessDecisionStrategyInterface $defaultStrategy
     */
    public function __construct(
        VoterRegistry $registry,
        private readonly Context $context,
        private readonly LoggerInterface $logger,
        private readonly AccessDecisionStrategyFactory $strategyFactory,
        private readonly AccessDecisionStrategyInterface $defaultStrategy
    ) {
        $this->voters = $registry->getVoters();
    }

    /**
     * Returns true, if any of the given attributes have been granted access by the responsible voter(s)
     *
     * @param string|string[]  $attributes
     * @param mixed $subject
     * @param string|null $strategy
     * @return bool
     * @throws AspectNotFoundException
     */
    public function vote(string|array $attributes, mixed $subject = null, ?string $strategy = null): bool
    {
        if ($this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn') === false) {
            $this->logger->warning('Invalid login. User is not logged in. 1733910000');
            return false;
        }

        $attributes = (array)$attributes;

        foreach ($attributes as $attribute) {
            if ($this->voteOnSingleAttribute($attribute, $subject, $strategy)) {
                return true;
            }
        }

        return false;
    }

    private function voteOnSingleAttribute(
        string $attribute,
        mixed $subject,
        ?string $strategy
    ): bool {
        $votes = [];

        foreach ($this->voters as $voter) {
            if ($voter->supports($attribute, $subject)) {
                $votes[] = $voter->voteOnAttribute($attribute, $subject);
            }
        }

        if (empty($votes)) {
            $this->logger->warning("No voter found for attribute: $attribute 1763537432");
            return false;
        }

        $decisionStrategy = $strategy
            ? $this->strategyFactory->create($strategy)
            : $this->defaultStrategy;

        return $decisionStrategy->decide($votes);
    }
}
