<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AdminCrudEvent.
 */
class AdminCrudEvent extends Event
{
    const DONT_TOUCH = 0;
    const CREATE = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * @var
     */
    private $item;
    /**
     * @var
     */
    private $action;
    /**
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    private $user;

    /**
     * @param $item
     * @param $action
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function __construct($item, $action, UserInterface $user = null)
    {
        $this->item = $item;
        $this->action = $action;
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     *
     * @return mixed
     */
    public function setAction($action)
    {
        return $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
