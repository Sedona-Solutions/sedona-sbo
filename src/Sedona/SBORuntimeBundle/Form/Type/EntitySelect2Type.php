<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class EntitySelect2Type
 * @package Sedona\SBORuntimeBundle\Form\Type
 */
class EntitySelect2Type extends EntityTextType {

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $copyProperty = [
        'placeholder'           => 'placeholder',
        'minimumInputLength'    => 'data-minimumInputLength',
        'maximumInputLength'    => 'data-maximumInputLength'
    ];

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, RouterInterface $router)
    {
        parent::__construct($om, $this->getName());
        $this->router = $router;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver
            ->setDefaults(array(
                'attr'  => [
                    "data-toggle" => 'select2-remote'
                ],
                'placeholder' => null,          // Initial value that is selected if no other selection is made.
                'searchRouteParams' => []       // parametre de la route de recherche...
            ))
            ->setOptional([
                'minimumInputLength',           // Number of characters necessary to start a search.
                'maximumInputLength'            // Maximum number of characters that can be entered for an input.
            ])
            ->setRequired([
                'class',
                'searchRouteName',              // route de recherche...
                'property',                     // proprrété sur lr quelle est fait la recheche
            ])
            ->setAllowedTypes([
                'class' => ['String'],
                'searchRouteName' => ['String'],
                'property' => ['String']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['autocomplete'] = 'off';
        if (array_key_exists('searchRouteName',$options)) {
            $view->vars['attr']['data-source'] = $this->router->generate($options['searchRouteName'], $options['searchRouteParams'])."?property=".$options['property'];
        }

        $object = $form->getData();
        if ($object != null && $object instanceof $options['class']) {
            $getterI = 'get'.ucfirst($options['primaryKey']);
            $getterP = 'get'.ucfirst($options['property']);
            if(method_exists($object, $getterI) && method_exists($object, $getterP)) {
                $view->vars['attr']['data-initSelection'] = '{"id":"'.$object->$getterI().'","text":"'.str_replace('"','\"',$object->$getterP()).'"}' ;
            } else if(method_exists($object, $getterI) && method_exists($object, '__toString')) {
                $view->vars['attr']['data-initSelection'] = '{"id":"'.$object->$getterI().'","text":"'.str_replace('"','\"',''.$object).'"}' ;
            }
        }

        if (array_key_exists('required',$options) && $options['required'] == false && array_key_exists('placeholder',$options) && empty($options['placeholder'])) {
            throw new \Exception('Le placeholder est obligatoire pour pouvoir supprimer la valeur');
        }

        foreach ($this->copyProperty as $optionProperty => $attrProperty) {
            if (array_key_exists($optionProperty,$options)) {
                $view->vars['attr'][$attrProperty] = $options[$optionProperty];
            }
        }

        parent::buildView($view,$form,$options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'entity_select2';
    }
}
