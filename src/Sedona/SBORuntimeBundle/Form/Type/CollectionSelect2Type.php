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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CollectionSelect2Type
 * @package Sedona\SBORuntimeBundle\Form\Type
 */
class CollectionSelect2Type extends EntityTextType {

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $copyProperty = [
        'multiple'              => 'multiple',
        'placeholder'           => 'placeholder',
        'minimumInputLength'    => 'data-minimumInputLength',
        'maximumInputLength'    => 'data-maximumInputLength',
        'maximumSelectionSize'  => 'data-maximumSelectionSize'
    ];

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, RouterInterface $router)
    {
        parent::__construct($om, $this->getName());
        $this->router = $router;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefaults(array(
                'multiple' => true,
                'attr'  => [
                    "data-toggle" => 'select2-remote'
                ],
                'placeholder' => null           // Initial value that is selected if no other selection is made.
            ))
            ->setDefined([
                'minimumInputLength',           // Number of characters necessary to start a search.
                'maximumInputLength',           // Maximum number of characters that can be entered for an input.
                'maximumSelectionSize'          //   The maximum number of items that can be selected in a multi-select control. If this number is less than 1 selection is not limited.
            ])
            ->setRequired([
                'class',
                'searchRouteName',              // route de recherche...
                'property',                     // proprrété sur lr quelle est fait la recheche
            ])
            ->setAllowedTypes('class', ['String'])
            ->setAllowedTypes('searchRouteName', ['String'])
            ->setAllowedTypes('property', ['String'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['autocomplete'] = 'off';
        if (array_key_exists('searchRouteName',$options)) {
            $view->vars['attr']['data-source'] = $this->router->generate($options['searchRouteName'])."?property=".$options['property'];
        }

        $collection = $form->getData();
        if ($collection != null && (is_array($collection) || $collection instanceof \Traversable)) {

            $getterI = 'get'.ucfirst($options['primaryKey']);
            $getterP = 'get'.ucfirst($options['property']);
            $result = [];
            foreach ($collection as $object) {
                if($object instanceof $options['class'] && method_exists($object, $getterI) && method_exists($object, $getterP)) {
                    $result[] = ["id"=>$object->$getterI(),"text"=> $object->$getterP() ];
                }
            }

            $view->vars['attr']['data-initSelection'] =  json_encode($result);
        }

        foreach ($this->copyProperty as $optionProperty => $attrProperty) {
            if (array_key_exists($optionProperty,$options) && empty($options[$optionProperty]) == false) {
                $view->vars['attr'][$attrProperty] = $options[$optionProperty];
            }
        }

        parent::buildView($view,$form,$options);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'collection_select2';
    }
}
