<?php

namespace Umanit\TranslationBundle\Twig;

use JetBrains\PhpStorm\ArrayShape;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigTest;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

class UmanitTranslationExtension extends AbstractExtension implements GlobalsInterface
{
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function getTests(): array
    {
        return [
            new TwigTest('translatable', function ($object) {
                return $object instanceof TranslatableInterface;
            }),
        ];
    }

    #[ArrayShape(['locales' => "array"])]
    public function getGlobals(): array
    {
        return [
            'locales' => $this->locales,
        ];
    }
}
