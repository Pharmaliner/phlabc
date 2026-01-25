<?php

use Pharmaline\PhlAbc\DependencyInjection\VoterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return function (ContainerBuilder $container) {
    $container->addCompilerPass(new VoterPass());
};
