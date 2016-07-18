<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle;

/**
 * Class ClassUtils.
 */
class ClassUtils
{
    /**
     * Return true if the given object use the given trait, FALSE if not.
     *
     * @param \ReflectionClass|string $class
     * @param string                  $traitName
     * @param bool                    $isRecursive
     *
     * @return bool
     */
    public static function hasTrait($class, $traitName, $isRecursive = false)
    {
        if (is_string($class)) {
            $class = new \ReflectionClass($class);
        }

        if (in_array($traitName, $class->getTraitNames(), true)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if ((false === $isRecursive) || (false === $parentClass) || (null === $parentClass)) {
            return false;
        }

        return static::hasTrait($parentClass, $traitName, $isRecursive);
    }
}
