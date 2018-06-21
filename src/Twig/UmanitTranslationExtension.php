<?php

namespace Umanit\TranslationBundle\Twig;

use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UmanitTranslationExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var array
     */
    private $locales;

    /**
     * UmanitTranslationExtension constructor.
     *
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('translatable', function ($object) {
                return $object instanceof TranslatableInterface;
            }),
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getGlobals()
    {
        return [
            'locales' => $this->locales,
        ];
    }

}
