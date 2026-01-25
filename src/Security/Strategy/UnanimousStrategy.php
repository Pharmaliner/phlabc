<?php

namespace Pharmaline\PhlAbc\Security\Strategy;

use Pharmaline\PhlAbc\Security\Voter\VoterInterface;
use Stringable;

/**
 * Grants access if only grant (or abstain) votes were received.
 *
 * If all voters abstained from voting, the decision will be based on the
 * allowIfAllAbstainDecisions property value (defaults to false).
 *
 *
 * This file is based on Symfony's AccessDecisionManager component.
 * (c) Fabien Potencier <fabien@symfony.com>
 */
final class UnanimousStrategy implements AccessDecisionStrategyInterface, Stringable
{
    public function __construct(
        private bool $allowIfAllAbstainDecisions = false,
    ) {
    }

    public function decide(array $results): bool
    {
        $grant = 0;
        foreach ($results as $result) {
            if ($result === VoterInterface::ACCESS_DENIED) {
                return false;
            }

            if ($result === VoterInterface::ACCESS_GRANTED) {
                ++$grant;
            }
        }

        // no deny votes
        if ($grant > 0) {
            return true;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    public function __toString(): string
    {
        return 'unanimous';
    }
}
