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
 * Class AdminAssociationActionEvent.
 */
class AdminAssociationActionEvent extends Event
{
    const DONT_TOUCH = 'none';

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
     * @var
     */
    private $target;

    /**
     * @param $item
     * @param $action
     * @param $target
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function __construct($item, $action, $target, UserInterface $user = null)
    {
        $this->item = $item;
        $this->action = $action;
        $this->user = $user;
        $this->target = $target;
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

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }
}
