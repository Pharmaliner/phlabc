<?php

namespace Pharmaline\PhlAbc\Security;

interface VoterInterface
{
    public function vote(string $permission, mixed $subject = null): bool;
}