<?php

namespace Pharmaline\PhlAbc\Security\Service;

use Pharmaline\PhlAbc\Security\Voter\VoterInterface;

class VoterRegistry
{
    /**
     * @var array<VoterInterface> $voters
     */
    private array $voters = [];

    public function registerVoter(VoterInterface $voter): void
    {
        $this->voters[] = $voter;
    }

    /**
     * @return VoterInterface[]
     */
    public function getVoters(): array
    {
        return $this->voters;
    }
}
