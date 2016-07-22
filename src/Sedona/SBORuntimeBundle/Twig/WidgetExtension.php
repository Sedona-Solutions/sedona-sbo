<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Twig;

use Twig_Environment;

/**
 * Class WidgetExtension.
 */
class WidgetExtension extends \Twig_Extension
{
    public function defaultDateFormat()
    {
        return 'DD/MM/YYYY';
    }

    public function defaultDateTimeFormat()
    {
        return 'DD/MM/YYYY HH:mm:ss';
    }

    public function defaultTimeFormat()
    {
        return 'HH:mm:ss';
    }

    public function renderWidget()
    {
    }

    public function addGlyphicon($text, $icon)
    {
        return '<i class="glyphicon glyphicon-'.$icon.' "></i> '.$text;
    }

    public function getFilters()
    {
        return array(
            'addGlyphicon' => new \Twig_SimpleFilter(
                'addGlyphicon',
                array($this, 'addGlyphicon'),
                array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'purifyLight' => new \Twig_SimpleFilter(
                'purifyLight',
                array($this, 'purifyLight'),
                array('is_safe' => array('html'))),
        );
    }

    public function purifyLight($text, $light_mode = false)
    {

//        <p>bio :) avec une <strong>iframe </strong>:</p>
//        <p><iframe src="http://www.sedona.fr"></iframe></p><p> et du JS : <script language="javascript">alert("hello");</script></p>


    //Remove iframe
        $text = preg_replace('/<\s*iframe(.*)>(.*)<\/iframe\s*>/isU', '', $text);

        $text = preg_replace('/<\s*iframe(.*)(\/?)>/isU', '', $text);

        //Remove script (js)
        $text = preg_replace('/<script(.*?)>(.*?)<\/script>/isU', '', $text);

        return $text;
    }


    public function getFunctions()
    {
        return array(
            'widget_box' => new \Twig_SimpleFunction('widget_box',
                                                     array($this, 'renderWidget'),
                                                     array('is_safe' => array('html'))),
            'default_date_format' => new \Twig_SimpleFunction(
                'default_date_format',
                array($this, 'defaultDateFormat')),
            'default_datetime_format' => new \Twig_SimpleFunction(
                'default_datetime_format',
                array($this, 'defaultDateTimeFormat')),
            'default_time_format' => new \Twig_SimpleFunction(
                'default_time_format',
                array($this, 'defaultTimeFormat')),
        );
    }

    public function getName()
    {
        return 'sbo_widget';
    }
}
