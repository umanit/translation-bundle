# Translation Bundle

This bundle intends to ease Doctrine entity translations.
Sonata Admin friendly with automatic integration. 

## Install

Register the bundle to your 'app/AppKernel.php'

```php
    new Umanit\TranslationBundle\UmanitTranslationBundle(),
```

Configure your available locales

```yaml
umanit_translation:
    locales: [en, fr, ja]
```

That's it!

## Usage

### Make your entity translatable

Implement `Umanit\TranslationBundle\Doctrine\TranslatableInterface` and use the trait 
`Umanit\TranslationBundle\Doctrine\ModelTranslatableTrait`on an entity you want to make translatable.
```php
<?php

namespace AppBundle\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * HomePage
 *
 * @ORM\Table(name="page")
 */
class Page implements TranslatableInterface
{
    use TranslatableTrait;
}
```

### Translate your entity

Use the service `umanit_translation.translator.entity_translator` to translate a source entity to a target language.

```php
$translatedEntity = $this->get('umanit_translation.translator.entity_translator')->getEntityTranslation($entity, 'fr');
```

The `$translatedEntity` will be persisted and flushed.

Every attribute of the source entity will be cloned into a new entity.

## Options

Usually, you don't wan't to get **all** of your entity's fields to be cloned. Some should be shared throughout all 
translations, some others should be emptied in a new translation. Two special annotations are provided in order to
solve this.

**@SharedAmongstTranslations**

Using this annotation will make the value of your field identical throughout all translations. Also, if you update this 
field in any translation, all the others will be synchronized.

**Note :** ManyToMany associations are not supported with SharedAmongstTranslations yet.


```php
<?php

namespace AppBundle\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;

/**
 * HomePage
 *
 * @ORM\Table(name="page")
 */
class Page implements TranslatableInterface
{
    use TranslatableTrait;
    
    /**
     * @var Application\Sonata\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="video_id", referencedColumnName="id")
     * @SharedAmongstTranslations()
     */
    protected $video;
    
}
```

**@EmptyOnTranslate**

This annotation will empty field when creating a new translation.

```php
<?php

namespace AppBundle\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;

/**
 * HomePage
 *
 * @ORM\Table(name="page")
 */
class Page implements TranslatableInterface
{
    use TranslatableTrait;
    
    // ...
    
    /**
     * @var Application\Sonata\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * @EmptyOnTranslate()
     */
    protected $image;
    
}
```

### Filtering your contents

To fetch your contents out of your database in the current locale, you'd usually do something like `$repository->findByLocale($request->getLocale())`.

Alternatively, you can use the provided filter that will automatically filter any Translatble entity by the current locale, every time you query the ORM.
This way, you can simply do `$repository->findAll()` instead of the previous example.

Add this to your `config.yml` file:

```yaml
#...
# Doctrine Configuration
doctrine:
    orm:
        filters:
            umanit_translation_locale_filter:
                class:   'Umanit\TranslationBundle\Doctrine\Filter\LocaleFilter'
                enabled: true
```  

## Integrating into SonataAdmin

The bundle will automatically add translations widgets in SonataAdmin if you're using it.
* The `list` view will add two columns `locale` and `translations`.
* The `edit` button on the `list` view will show a dropdown to select the desired language to be edited.
* The tab menu on the `edit` view will have an entry to translate the edited content.

If you want to define a default locale for the admin, configure the `default_locale`.

```yaml
umanit_translation:
    # ...
    default_locale: en
```
The admin will then show only the english contents on the list view.