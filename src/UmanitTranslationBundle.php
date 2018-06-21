<?php

namespace Umanit\TranslationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Umanit\TranslationBundle\DependencyInjection\Compiler\TranslationHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UmanitTranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TranslationHandlerPass());
    }
}
