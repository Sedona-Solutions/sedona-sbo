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
use JMS\DiExtraBundle\Annotation as DI;
use Sedona\SBORuntimeBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class EntityTextType
 * @package Sedona\SBORuntimeBundle\Form\Type
 */
class EntityTextType extends TextType
{

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $name = 'entity_text')
    {
        $this->om = $om;
        $this->name = $name;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $transformer = new EntityToIdTransformer($this->om, $options['class'], $options['primaryKey'], $options['multiple']);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'primaryKey' => 'id',
            'class' => null,
            'invalid_message' => 'The selected item does not exist',
            'multiple' => false,
        ));
    }

    public function getName()
    {
        return $this->name;
    }
}
