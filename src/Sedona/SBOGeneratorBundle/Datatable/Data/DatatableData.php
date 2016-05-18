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

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatatableData
 * @package Sedona\SBOGeneratorBundle\Datatable\Data
 */
class DatatableData extends \Sg\DatatablesBundle\Datatable\Data\DatatableData
{

    /**
     * Set columns.
     *
     * @return $this
     */
    private function setColumns()
    {
        $this->datatableQuery->setSelectColumns($this->selectColumns);
        $this->datatableQuery->setAllColumns($this->allColumns);
        $this->datatableQuery->setJoins($this->joins);
        $this->datatableQuery->setSearchColumns($this->searchColumns);
        $this->datatableQuery->setOrderColumns($this->orderColumns);

        return $this;
    }

    /**
     * Build query.
     *
     * @return $this
     */
    private function buildQuery()
    {
        $this->datatableQuery->setSelectFrom();
        $this->datatableQuery->setLeftJoins();
        $this->datatableQuery->setWhere();
        $this->datatableQuery->setWhereCallbacks();
        $this->datatableQuery->setOrderBy();
        $this->datatableQuery->setLimit();

        return $this;
    }



    //-------------------------------------------------
    // DatatableDataInterface
    //-------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        $this->setColumns();
        $this->buildQuery();

        $fresults = new Paginator($this->datatableQuery->execute(), true);
        $output = array("data" => array());

        $outputHeader = array(
            "draw" => (integer) $this->requestParams["draw"],
            "recordsTotal" => (integer) $this->datatableQuery->getCountAllResults($this->rootEntityIdentifier),
            "recordsFiltered" => (integer) $this->datatableQuery->getCountFilteredResults($this->rootEntityIdentifier)
        );

        foreach ($fresults as $item) {
            if (is_callable($this->lineFormatter)) {
                $callable = $this->lineFormatter;
                $item = call_user_func($callable, $item, $outputHeader);
            }

            $output["data"][] = $item;
        }

        $this->response = array_merge($outputHeader, $output);

        $json = $this->serializer->serialize($this->response, "json");
        $response = new Response($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

}
