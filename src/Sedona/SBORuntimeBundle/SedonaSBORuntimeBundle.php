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

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SedonaSBORuntimeBundle
 * @package Sedona\SBORuntimeBundle
 */
class SedonaSBORuntimeBundle extends Bundle
{
    public function getParent()
    {
        return 'AvanzuAdminThemeBundle';
    }
}
