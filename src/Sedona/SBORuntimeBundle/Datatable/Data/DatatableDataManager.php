<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Datatable\Data;

use Sg\DatatablesBundle\Datatable\View\DatatableViewInterface;
use Sg\DatatablesBundle\Datatable\Data\DatatableDataManager as DatatableDataManagerBase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DatatableDataManager.
 */
class DatatableDataManager extends DatatableDataManagerBase
{
    /**
     * The request.
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
     * Configuration settings.
     *
     * @var array
     */
    private $configs;

    /**
     * True if the LiipImagineBundle is installed.
     *
     * @var bool
     */
    private $imagineBundle;

    /**
     * True if GedmoDoctrineExtensions installed.
     *
     * @var bool
     */
    private $doctrineExtensions;

    /**
     * The locale.
     *
     * @var string
     */
    private $locale;

    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param RequestStack $requestStack
     * @param Serializer   $serializer
     * @param array        $configs
     * @param array        $bundles
     */
    public function __construct(RequestStack $requestStack, Serializer $serializer, array $configs, array $bundles)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->serializer = $serializer;
        $this->configs = $configs;
        $this->imagineBundle = false;
        $this->doctrineExtensions = false;

        if (true === class_exists('Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')) {
            $this->doctrineExtensions = true;
        }

        if (true === array_key_exists('LiipImagineBundle', $bundles)) {
            $this->imagineBundle = true;
        }

        $this->locale = $this->request ? $this->request->getLocale() : "en"; // Force default to "en" when no request (ie command line, must be unit test)
    }

    //-------------------------------------------------
    // Public
    //-------------------------------------------------

    /**
     * Get query.
     *
     * @param DatatableViewInterface $datatableView
     *
     * @return DatatableQuery
     */
    public function getQueryFrom(DatatableViewInterface $datatableView)
    {
        $twig = $datatableView->getTwig();

        $type = $datatableView->getAjax()->getType();
        $parameterBag = null;

        if ('GET' === strtoupper($type)) {
            $parameterBag = $this->request->query;
        }

        if ('POST' === strtoupper($type)) {
            $parameterBag = $this->request->request;
        }

        $params = $parameterBag->all();
        $query = new DatatableQuery(
            $this->serializer,
            $params,
            $datatableView,
            $this->configs,
            $twig,
            $this->imagineBundle,
            $this->doctrineExtensions,
            $this->locale
        );

        return $query;
    }
}
