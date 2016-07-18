<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBOGeneratorBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator as BaseGenerator;

/**
 * Class Generator.
 */
class Generator extends BaseGenerator
{
    private $SBOskeletonDirs;

    /**
     * Sets an array of directories to look for templates.
     *
     * The directories must be sorted from the most specific to the most
     * directory.
     *
     * @param array $skeletonDirs An array of skeleton dirs
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $this->SBOskeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @return array $fields
     */
    protected function getFieldsFromMetadata($metadata)
    {
        $fields = $metadata->fieldMappings;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            foreach ($metadata->identifier as $oneId) {
                unset($fields[$oneId]);
            }
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if (in_array($relation['type'], [ClassMetadataInfo::MANY_TO_ONE, ClassMetadataInfo::ONE_TO_MANY])) {
                $fields[$fieldName] = $relation;
            } elseif ($relation['type'] == ClassMetadataInfo::MANY_TO_MANY && (empty($relation['mappedBy']) == false  || empty($relation['inversedBy'])  == false)) {
                /*|| $relation['targetEntity'] == $relation['sourceEntity']*/
                $fields[$fieldName] = $relation;
            }
        }

        return $fields;
    }

    /**
     * @param $template
     * @param $parameters
     *
     * @return string
     */
    protected function render($template, $parameters)
    {
        $option = array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        );

        $self = $this;
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->SBOskeletonDirs), $option);

        $twig->addFunction(new \Twig_SimpleFunction(
            'renderFile',
            function ($template, $target, $subParameters = []) use ($self,$parameters) {
                $self->renderFile($template, $target, array_merge($parameters, $subParameters));

                return '';
            },
            $option
        ));

        $twig->addFunction(new \Twig_SimpleFunction(
            'method_exists',
            function ($object, $methodeName) {
                return method_exists($object, $methodeName);
            },
            $option
        ));

        return $twig->render($template, $parameters);
    }
}
