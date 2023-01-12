<?php

namespace Umanit\TranslationBundle\Controller\Sonata;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Umanit\TranslationBundle\Translation\EntityTranslator;

class TranslatableCRUDController extends CRUDController
{
    private EntityTranslator $translator;
    private EntityManagerInterface $em;

    public function __construct(EntityTranslator $translator, EntityManagerInterface $em)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * Translate an entity
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function translate(Request $request): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        $locale = $request->get('newLocale');
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $newObject = $this->admin->getModelManager()->findOneBy(\get_class($object), [
            'tuuid'  => $object->getTuuid(),
            'locale' => $locale,
        ]);

        if (empty($newObject)) {
            $this->admin->checkAccess('edit', $object);

            $newObject = $this->translator->translate($object, $locale);

            $this->em->persist($newObject);
            $this->em->flush();

            $this->addFlash('sonata_flash_success', 'Translated successfully!');
        }

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $newObject->getId()]));
    }
}
