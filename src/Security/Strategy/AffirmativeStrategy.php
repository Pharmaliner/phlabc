<?php

namespace Pharmaline\PhlAbc\Security\Strategy;

use Pharmaline\PhlAbc\Security\Voter\VoterInterface;
use Stringable;

/**
 * Grants access if at least 1 voter returns an affirmative response.
 *
 * If all voters abstained from voting, the decision will be based on the
 * allowIfAllAbstainDecisions property value (defaults to false).
 *
 *
 * This file is based on Symfony's AccessDecisionManager component.
 * * (c) Fabien Potencier <fabien@symfony.com>
 */
final class AffirmativeStrategy implements AccessDecisionStrategyInterface, Stringable
{
    public function __construct(
        private bool $allowIfAllAbstainDecisions = false,
    ) {
    }

    public function decide(array $results): bool
    {
        $deny = 0;
        foreach ($results as $result) {
            if ($result === VoterInterface::ACCESS_GRANTED) {
                return true;
            }

            if ($result === VoterInterface::ACCESS_DENIED) {
                ++$deny;
            }
        }


        if ($deny > 0) {
            return false;
        }

        return $this->allowIfAllAbstainDecisions;
    }

    public function __toString(): string
    {
        return 'affirmative';
    }
}
