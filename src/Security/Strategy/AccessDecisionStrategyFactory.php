<?php

namespace Pharmaline\PhlAbc\Security\Strategy;

use InvalidArgumentException;

class AccessDecisionStrategyFactory
{
    public const STRATEGY_AFFIRMATIVE = 'affirmative';
    public const STRATEGY_UNANIMOUS = 'unanimous';

    public function __construct(
        private readonly AffirmativeStrategy $affirmative,
        private readonly UnanimousStrategy $unanimous,
    ) {
    }

    public function create(string $strategyName): AccessDecisionStrategyInterface
    {
        return match ($strategyName) {
            self::STRATEGY_AFFIRMATIVE => $this->affirmative,
            self::STRATEGY_UNANIMOUS => $this->unanimous,
            default => throw new InvalidArgumentException(
                "Unknown strategy: $strategyName. Valid: affirmative, unanimous, consensus, priority",
                1766066382
            ),
        };
    }
}
