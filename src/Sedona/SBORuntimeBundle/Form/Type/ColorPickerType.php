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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ColorPickerType
 * @package Sedona\SBORuntimeBundle\Form\Type
 */
class ColorPickerType extends TextType {

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefaults(array(
                'color-withaddon' => true,
                'color-format'    => null,            // 	If not false, forces the color format to be hex, rgb or rgba, otherwise the format is automatically detected.
                //container 	string or jQuery Element 	false 	If not false, the picker will be contained inside this element, otherwise it will be appended to the document body.
                //component 	string or jQuery Element 	'.add-on, .input-group-addon' 	Children selector for the component or element that trigger the colorpicker and which background color will change (needs an inner <i> element).
                //input 	string or jQuery Element 	'input' 	Children selector for the input that will store the picker selected value.
                'color-horizontal'=> null,        // If true, the hue and alpha channel bars will be rendered horizontally, above the saturation selector.
                'color-template'  => null          // Customizes the default colorpicker HTML template.
            ))
            ->setDefined([
            ])
            ->setRequired([
                'color-withaddon'
            ])
            ->setAllowedValues('color-format', [null,'hex','rgb','rgba'])
            ->setAllowedValues('color-horizontal', [null,true,false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $optionsColorPicker = [];
        if (array_key_exists('color-format',$options) && empty($options['color-format']) == false) {
            $optionsColorPicker['format']  = $options['color-format'];
        }
        if (array_key_exists('color-horizontal',$options) && empty($options['color-horizontal']) == false) {
            $optionsColorPicker['horizontal']  = $options['color-horizontal'];
        }
        if (array_key_exists('color-template',$options) && empty($options['color-template']) == false) {
            $optionsColorPicker['template']  = $options['color-template'];
        }

        $view->vars['optionsColorPicker'] = json_encode($optionsColorPicker);
        $view->vars['withAddon'] = $options['color-withaddon'] == true;
        parent::buildView($view,$form,$options);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'colorpicker';
    }

}
