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
 * Class WidgetExtension
 * @package Sedona\SBORuntimeBundle\Twig
 */
class WidgetExtension extends \Twig_Extension
{
    /**
     * @var Twig_Environment
     */
    protected $env;

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
            'purify' => new \Twig_SimpleFilter(
                'purify',
                array($this, 'purify'),
                array('is_safe' => array('html')))
        );
    }

    public function purify($text, $light_mode = false)
    {
        //Version with preg_replace => light mode
        if($light_mode) {
            //Remove iframe
            $text = preg_replace('/<iframe(.*?)>(.*?)<\/iframe>/is', '', $text);
            //Remove script (js)
            $text = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $text);
            return $text;
        }

        //Version with purifier => heavy mode (by default)
        $config = \HTMLPurifier_Config::createDefault();
        $config->set("HTML.ForbiddenElements", ['script', 'iframe']);
        $purificateur = new \HTMLPurifier($config);
        return $purificateur->purify($text);
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

    public function initRuntime(Twig_Environment $environment)
    {
        $this->env = $environment;
    }

    public function getName()
    {
        return 'sbo_widget';
    }
}
