<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityToIdTransformer
 * @package Sedona\SBORuntimeBundle\Form\DataTransformer
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $primaryKey;

    /**
     * @var boolean
     */
    private $multiple;

    /**
     * @var string
     */
    private $glue;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager    $om
     * @param string                                        $class Class : 'AcmeTaskBundle:Issue'
     * @param string                                        $primaryKey Property used as primary key
     * @param boolean                                       $multiple
     * @param string                                        $glue
     */
    public function __construct(ObjectManager $om, $class, $primaryKey = 'code', $multiple = false, $glue = ',')
    {
        $this->om = $om;
        $this->class = $class;
        $this->primaryKey = $primaryKey;
        $this->multiple = $multiple;
        $this->glue = $glue;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Object|array|null $item
     * @return string
     */
    public function transform($item)
    {
        if (null === $item) {
            return '';
        }

        $getter = 'get'.ucfirst($this->primaryKey);
        if ($this->multiple && (is_array($item) || $item instanceof \Traversable)) {
            $result = [];

            foreach ($item as $it) {
                if (method_exists($it, $getter)) {
                    $result[] = $it->$getter();
                } elseif (method_exists($it, 'getId')) {
                    $result[] = $it->getId();
                }
            }

            return implode($this->glue, $result);
        }

        if (method_exists($item, $getter)) {
            return $item->$getter();
        }
        if (method_exists($item, 'getId')) {
            return $item->getId();
        }

        return '';
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $number
     * @return Object|array|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($key)
    {

        if (!$key) {
            return null;
        }

        if ($this->multiple) {
            $keys = explode($this->glue, $key);
            $items = $this->om
                ->getRepository($this->class)
                ->findBy(array($this->primaryKey => $keys))
            ;

            if (count($keys) != count($items)) {
                throw new TransformationFailedException(sprintf("Can't find all items in %s this keys", $key));
            }

            return $items;
        }

        $item = $this->om
            ->getRepository($this->class)
            ->findOneBy(array($this->primaryKey => $key))
        ;

        if (null === $item) {
            throw new TransformationFailedException(sprintf("Can't find the %s item", $key));
        }

        return $item;
    }

}
