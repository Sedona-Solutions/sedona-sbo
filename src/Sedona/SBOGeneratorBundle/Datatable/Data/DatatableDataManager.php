<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBOGeneratorBundle\Datatable\Data;

use Sg\DatatablesBundle\Datatable\Data\DatatableQuery;
use Sg\DatatablesBundle\Datatable\View\DatatableViewInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class DatatableDataManager.
 */
class DatatableDataManager extends \Sg\DatatablesBundle\Datatable\Data\DatatableDataManager
{
    /**
     * The doctrine service.
     *
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * The request service.
     *
     * @var Request
     */
    private $request;

    /**
     * The serializer service.
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Holds request parameters.
     *
     * @var ParameterBag
     */
    private $parameterBag;

    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param RegistryInterface $doctrine   The doctrine service
     * @param Request           $request    The request service
     * @param Serializer        $serializer The serializer service
     */
    public function __construct(RegistryInterface $doctrine, Request $request, Serializer $serializer)
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->parameterBag = null;
    }

    /**
     * Get Datatable.
     *
     * @param DatatableViewInterface $datatableView
     *
     * @return DatatableData
     */
    public function getDatatable(DatatableViewInterface $datatableView)
    {
        $type = $datatableView->getAjax()->getType();
        $entity = $datatableView->getEntity();

        if ('GET' === strtoupper($type)) {
            $this->parameterBag = $this->request->query;
        }

        if ('POST' === strtoupper($type)) {
            $this->parameterBag = $this->request->request;
        }

        $params = $this->parameterBag->all();

        /**
         * @var \Doctrine\ORM\Mapping\ClassMetadata
         */
        $metadata = $this->doctrine->getManager()->getClassMetadata($entity);

        /**
         * @var \Doctrine\ORM\EntityManager
         */
        $em = $this->doctrine->getManager();

        $datatableQuery = new DatatableQuery($params, $metadata, $em);
        $virtualColumns = $datatableView->getColumnBuilder()->getVirtualColumnNames();
        $datatableData = new DatatableData($params, $metadata, $em, $this->serializer, $datatableQuery, $virtualColumns);
        $datatableData->setLineFormatter($datatableView->getLineFormatter());

        return $datatableData;
    }
}
