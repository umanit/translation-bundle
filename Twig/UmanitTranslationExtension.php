<?php

namespace  Umanit\TranslationBundle\Twig;

use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UmanitTranslationExtension extends \Twig_Extension
{
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('translatable', function ($object) {
                return $object instanceof TranslatableInterface;
            }),
        ];
    }
}
