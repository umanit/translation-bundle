# Translation Bundle

This bundle intends to ease Doctrine entity translations.
Unlike most translations libraries, every translation is stored in the same table as the source entity.

## Features

* Add translations without changing existing entities
* Translation fields are stored in the same table (no expensive joins)
* Supports inherited entities
* Handle more than just text fields
* Sonata admin integration
* Auto-population of translated relations

## Install

Register the bundle to your 'app/AppKernel.php'

```php
    new Umanit\TranslationBundle\UmanitTranslationBundle(),
```

Configure your available locales and, optionally, the default one

```yaml
umanit_translation:
    locales: [en, fr, ja]
    default_locale: en
```

That's it!

### Integration with Sonata Admin

You will need to add extra stylesheets and JavaScript to your admin interface:

```yaml
sonata_admin:
    assets:
        extra_stylesheets:
            - 'bundles/umanittranslation/css/admin-sonata.css'
        extra_javascripts:
            - 'bundles/umanittranslation/js/admin-filters.js'
```

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
$translatedEntity = $this->get('umanit_translation.translator.entity_translator')->translate($entity, 'fr');
```

The `$translatedEntity` will be persisted.

Every attribute of the source entity will be cloned into a new entity.

## Options

Usually, you don't wan't to get **all** of your entity's fields to be cloned. Some should be shared throughout all 
translations, some others should be emptied in a new translation. Two special annotations are provided in order to
solve this.

**@SharedAmongstTranslations**

Using this annotation will make the value of your field identical throughout all translations. Also, if you update this 
field in any translation, all the others will be synchronized. 
If the attribute is a relation to a translatable entity, it will associate the correct translation to each language.

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

#### (Optional) Disable the filter for a specific firewall
Usually you'll need to administrate your contents.
For doing so, you can disable the filter by configuring the disabled_firewalls option. 

```yaml
# app/config/config.yml
umanit_translation:
    disabled_firewalls: ['admin']
```

## Advanced usage

You can alter the entities to translate or translated, before and after translation using the `Umanit\TranslationBundle\Event\TranslateEvent`

- `TranslateEvent::PRE_TRANSLATE` called before starting to translate the properties. The new translation is just instanciate with the right `oid` and `locale`
- `TranslateEvent::POST_TRANSLATE` called after saving the translation

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

## Integration with DoctrineSingletonBundle

The bundle will automatically works with the [Doctrine Singleton Bundle](https://github.com/umanit/doctrine-singleton-bundle). If your singleton has the TranslatableInterface, it will be possible to get one instance per locale. 
