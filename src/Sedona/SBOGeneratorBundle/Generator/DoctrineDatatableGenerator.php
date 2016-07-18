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
 * Generates a datatable class based on a Doctrine entity.
 */
class DoctrineDatatableGenerator extends Generator
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

        $this->className = $entityClass.'Datatable';
        $dirPath = $bundle->getPath().'/Datatables';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'Datatable.php';

        if (file_exists($this->classPath) && !$forceOverwrite) {
            throw new \RuntimeException(sprintf('Unable to generate the %s datatable class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The datatable generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $params = array(
            'fields' => $this->getFieldsFromMetadata($metadata),
            'namespace' => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class' => $entityClass,
            'entity' => $entityClass,
            'bundle' => $bundle->getName(),
            'form_class' => $this->className,
            'form_type_name' => strtolower('admin_'.substr($this->className, 0, -4)), //str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.substr($this->className, 0, -4)),
        );

        // Generate base generator if not exists
        $baseControllerFile = $dirPath.'/AbstractCrudDatatableView.php';
        if (!file_exists($baseControllerFile)) {
            $this->renderFile('datatable/AbstractCrudDatatableView.php.twig', $baseControllerFile, $params);
        }

        $this->renderFile('datatable/Datatable.php.twig', $this->classPath, $params);
    }
}
