<?php

namespace Pharmaline\PhlAbc\Security\Strategy;

interface AccessDecisionStrategyInterface
{
    /**
     * @param int[] $results
     */
    public function decide(array $results): bool;
}
