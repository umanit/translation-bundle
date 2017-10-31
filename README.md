# Translation Bundle

This bundle intends to ease entity translations.

## Install

Register the bundle to your 'app/AppKernel.php'

```php
    new Umanit\TranslationBundle\UmanitTranslationBundle(),
```

Configure your available locales in `app/config.yml`

```yaml
parameters:
    locale: en
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

Use the following guide to integrate TranslationBundle within your SonataAdmin application.

1. Create translatable admin class

```php
<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

abstract class AbstractTranslatableAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translate', $this->getRouterIdParameter() . '/translate/{newLocale}');
    }

}

```

**/!\ Every admin of a translatable entity must extend this class instead of Sonata's one.**


1. Create a custom CRUD Controller.

```php
<?php

namespace AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TranslatableCRUDController extends CRUDController
{
    public function translateAction()
    {
        $request = $this->getRequest();

        $id     = $request->get($this->admin->getIdParameter());
        $locale = $request->get('newLocale');
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $newObject = $this->admin->getModelManager()->findOneBy(get_class($object), ['oid' => $object->getOid(), 'locale' => $locale]);

        if (empty($newObject)) {
            $this->admin->checkAccess('edit', $object);

            $newObject = $this->get('umanit_translation.translator.entity_translator')->getEntityTranslation($object, $locale);
            $this->admin->create($newObject);

            $this->addFlash('sonata_flash_success', 'Translated successfully');
        }

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $newObject->getId()]));
    }
}
```

1. Use this controller for all your translatable entites
```yaml
    app.admin.content.home_page:
        class: AppBundle\Admin\Content\HomePageAdmin
        arguments: [~, AppBundle\Entity\Content\Page, 'AppBundle:Admin\TranslatableCRUD']
        tags:
            - { name: sonata.admin, manager_type: orm, group: 'Content', label: 'Home Page' }

```

1. Add a locale switcher to SonataAdmin

Start by [overriding Sonata's layout](https://symfony.com/doc/master/bundles/SonataAdminBundle/reference/templates.html#configuring-templates) if you haven't already.

Next add this piece of code:

```twig
{% block sonata_admin_content_actions_wrappers %}
    {{ parent() }}
    {% if object is defined and object is translatable and object.oid is not empty and admin is defined and action is defined and action == 'edit' and locales|length > 1 %}
        <div class="nav navbar-right btn-group">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown sonata-actions">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ 'Language'|trans({}, 'SonataAdminBundle') }} {{ object.locale }}
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu" role="menu">
                        {% for locale in locales %}
                            {% if object.locale != locale %}
                                <li>
                                    <a href="{{ admin.generateObjectUrl('translate', object, {'newLocale': locale}) }}" class="sonata-action-element">{{ locale }}</a>
                                </li>
                            {% endif %}
                        {% endfor %}
                    </ul>
                </li>
            </ul>
        </div>
    {% endif %}
{% endblock sonata_admin_content_actions_wrappers %}
```

A language switcher will appear in your entity's edit form
