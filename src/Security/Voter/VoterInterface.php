<?php

namespace Pharmaline\PhlAbc\Security\Voter;

interface VoterInterface
{
    public const ACCESS_GRANTED = 1;
    public const ACCESS_ABSTAIN = 0;
    public const ACCESS_DENIED = -1;

    public function supports(string $attribute, mixed $subject): bool;

    /**
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     */
    public function voteOnAttribute(string $attribute, mixed $subject): int;
}
