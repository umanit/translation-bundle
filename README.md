# Translation Bundle

This bundle intends to ease Doctrine entity translations.
Unlike most translations libraries, every translation is stored in the same table as the source entity.

## Features

* Add translations without changing existing entities
* Translation fields are stored in the same table (no expensive joins)
* Supports inherited entities
* Handle more than just text fields
* Integration with Sonata and EasyAdmin
* Auto-population of translated relations

## Install

```
composer require umanit/translation-bundle
```

Register the bundle to your `app/AppKernel.php` if it's not done automatically. 

```php
    new Umanit\TranslationBundle\UmanitTranslationBundle(),
```

Configure your available locales and, optionally, the default one:

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

### Integration with EasyAdmin

You'll need to add the assets to your `DashboardController`:

```php
    public function configureAssets(): Assets
    {
        return parent::configureAssets()->addJsFile('bundles/umanittranslation/js/easyadmin.js');
    }
```

## Usage

### Make your entity translatable

Implement `Umanit\TranslationBundle\Doctrine\TranslatableInterface` and use the trait 
`Umanit\TranslationBundle\Doctrine\ModelTranslatableTrait`on an entity you want to make translatable.
```php
<?php

namespace App\Entity\Content;

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

The `$translatedEntity` will be persisted with Sonata, jumpstarted with EasyAdmin: with both, you'll be redirected to the
edit form.

Every attribute of the source entity will be cloned into a new entity, unless specified otherwise with the `EmptyOnTranslate`
attribute.

## Options

Usually, you don't wan't to get **all** fields of your entity to be cloned. Some should be shared throughout all 
translations, others should be emptied in a new translation. Two special attributes are provided in order to
solve this.

**SharedAmongstTranslations**

Using this attribute will make the value of your field identical throughout all translations: if you update this 
field in any translation, all the others will be synchronized. 
If the attribute is a relation to a translatable entity, it will associate the correct translation to each language.

**Note :** `ManyToMany` associations are not supported with `SharedAmongstTranslations` yet.

```php
<?php

namespace App\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;

#[ORM\Table(name: "page")]
class Page implements TranslatableInterface
{
    use TranslatableTrait;
    
     #[ORM\ManyToOne(targetEntity: "Application\Sonata\MediaBundle\Entity\Media", cascade: {"persist"})]
     #[ORM\JoinColumn(name: "video_id", referencedColumnName: "id")]
     #[SharedAmongstTranslations]
    protected Application\Sonata\MediaBundle\Entity\Media $video;
    
}
```

**EmptyOnTranslate**

This attribute will empty the field when creating a new translation.

```php
<?php

namespace App\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;

 #[ORM\Table(name: "page")]
class Page implements TranslatableInterface
{
    use TranslatableTrait;
    
    // ...
    
     #[ORM\ManyToOne(targetEntity: "Application\Sonata\MediaBundle\Entity\Media", cascade: {"persist"})]
     #[ORM\JoinColumn(name: "image_id", referencedColumnName: "id")]
     #[EmptyOnTranslate]
    protected Application\Sonata\MediaBundle\Entity\Media $image;
    
}
```

### Filtering your contents

To fetch your contents out of your database in the current locale, you'd usually do something like `$repository->findByLocale($request->getLocale())`.

Alternatively, you can use the provided filter that will automatically filter any Translatable entity by the current locale, every time you query the ORM.
This way, you can simply do `$repository->findAll()` instead of the previous example.

Add this to your `config.yml` file:

```yaml
# Doctrine Configuration
doctrine:
    orm:
        filters:
            # ...
            umanit_translation_locale_filter:
                class:   'Umanit\TranslationBundle\Doctrine\Filter\LocaleFilter'
                enabled: true
```  

#### (Optional) Disable the filter for a specific firewall

Usually you'll need to administrate your contents.
For doing so, you can disable the filter by configuring the disabled_firewalls option. 

```yaml
umanit_translation:
    # ...
    disabled_firewalls: ['admin']
```

## Advanced usage

You can alter the entities to translate or translated, before and after translation using the `Umanit\TranslationBundle\Event\TranslateEvent`

- `TranslateEvent::PRE_TRANSLATE` called before starting to translate the properties. The new translation is just instanciated with the right `oid` and `locale`
- `TranslateEvent::POST_TRANSLATE` called after saving the translation

## Integration with admin bundles
### Sonata

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

### EasyAdmin 4

* Have your controllers extend `AbstractTranslatableCRUDController` instead of `AbstractCrudController` from EasyAdmin. This will:
  * Add `TUUID` and `locale` as columns in the index view, as well as replace the edit action with links to translate to or edit a given locale
  * Add a translate dropdown with links to other locales on the edit view (translated or not)

The bundle **does not** automatically apply a filter to only show objects translated in the default locale (if it exists). The filter, however, is
activated and can be manually triggered to display existing objects for the desired locale.

## Integration with DoctrineSingletonBundle

The bundle will automatically works with the [Doctrine Singleton Bundle](https://github.com/umanit/doctrine-singleton-bundle). If your singleton has the TranslatableInterface, it will be possible to get one instance per locale. 
