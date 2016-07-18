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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Generates a YML translation based on a Doctrine entity.
 */
class DoctrineTranslationGenerator extends Generator
{
    private $filesystem;
    private $className;
    private $classPath;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param BundleInterface   $bundle   The bundle in which to create the class
     * @param string            $entity   The entity relative class name
     * @param ClassMetadataInfo $metadata The entity metadata class
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $forceOverwrite)
    {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $langs = ['fr', 'en'];

        $this->className = $entityClass.'Type';
        $dirPath = $bundle->getPath().'/Resources/translations';

        foreach ($langs as $lang) {
            $this->classPath = $dirPath.'/'.'admin.'.$lang.'.yml';
            $translations = '';
            $entityHeader = str_repeat(' ', 4).strtolower($entityClass).":\n";

            if (file_exists($this->classPath)) {
                $translations = file_get_contents($this->classPath);

                if (strpos($translations, $entityHeader) !== false) {
                    throw new \RuntimeException(sprintf('Unable to generate the %s entity translation as it already exists under the %s file', $this->className, $this->classPath));
                }
            } else {
                $translations = $this->render('translations/admin.'.$lang.'.yml.twig', []);
            }

            if (count($metadata->identifier) > 1) {
                throw new \RuntimeException('The form generator does not support entity classes with multiple primary keys.');
            }

            // add a trailing \n if not exists
            if (substr($translations, -1, 1) != "\n") {
                $translations .= "\n";
            }

            $parts = explode('\\', $entity);
            array_pop($parts);

            $translations .= $entityHeader;
            $translations .= str_repeat(' ', 8).'entity_name: '.str_replace('_', ' ', ucfirst(strtolower($entityClass)))."\n";

            foreach ($this->getFieldsFromMetadata($metadata) as $field => $meta) {
                $translations .= str_repeat(' ', 8).$field.': '.str_replace('_', ' ', ucfirst(strtolower($field)))."\n";
            }

            file_put_contents($this->classPath, $translations);
        }
    }
}
