<?php

namespace Pharmaline\PhlAbc\Security;

class VoterServiceLocator
{
    private static ?VoterInterface $voter = null;

    public static function setVoter(VoterInterface $voter): void
    {
        self::$voter = $voter;
    }

    public static function getVoter(): VoterInterface
    {
        if (!self::$voter) {
            throw new \RuntimeException('VoterInterface not set in ServiceLocator.');
        }

        return self::$voter;
    }
}