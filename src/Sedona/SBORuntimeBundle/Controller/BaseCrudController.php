<?php

/*
 * This file is part of Sedona Back-Office Bundles.
 *
 * (c) Sedona <http://www.sedona.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sedona\SBORuntimeBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Sedona\SBORuntimeBundle\Event\AdminAssociationActionEvent;
use Sedona\SBORuntimeBundle\Event\AdminCrudEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base CRUD Controller.
 */
abstract class BaseCrudController extends Controller
{
    protected $route_name = 'admin_default';
    protected $bundle_name = 'DefaultBundle';
    protected $entity_name = 'Default';

    /*
     * default URL (can be overloaded)
     */
    protected function getEditUrl($entity)
    {
        return $this->getUrl($entity, '_edit');
    }
    protected function getNewUrl($entity)
    {
        return $this->getUrl($entity, '_new');
    }
    protected function getShowUrl($entity)
    {
        return $this->getUrl($entity, '_show');
    }
    protected function getListUrl()
    {
        return $this->generateUrl($this->route_name.'_list');
    }

    /*
     * default Flash messages (can be overloaded)
     */
    protected function getFlashSavedMessage()
    {
        return 'crud.message.saved';
    }
    protected function getFlashDeletedMessage()
    {
        return 'crud.message.deleted';
    }
    protected function getFlashCreatedMessage()
    {
        return 'crud.message.created';
    }

    /*
     * default templates (can be overloaded)
     */
    protected function getEditTemplate()
    {
        return $this->bundle_name.':Admin/'.$this->entity_name.':'.'edit.html.twig';
    }
    protected function getNewTemplate()
    {
        return $this->bundle_name.':Admin/'.$this->entity_name.':'.'new.html.twig';
    }
    protected function getShowTemplate()
    {
        return $this->bundle_name.':Admin/'.$this->entity_name.':'.'show.html.twig';
    }

    /**
     * @param $entity
     * @param $mode
     *
     * @return string
     */
    protected function getUrl($entity, $mode)
    {
        return $this->generateUrl($this->route_name.$mode, array('id' => $entity->getId()));
    }

    /**
     * Generic edit controller helper.
     *
     * @param $entity
     * @param Request $request
     *
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function manageEdit($entity, Request $request, FormTypeInterface $form)
    {
        $form = $this->createForm($form, $entity, array(
            'action' => $this->getEditUrl($entity),
            'method' => 'POST',
        ));

        $form
            ->add('save', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-danger')));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('save')->isClicked()) {
                if ($form->isValid()) {
                    return $this->crudAction($entity, AdminCrudEvent::UPDATE);
                }
            } elseif ($form->get('delete')->isClicked()) {
                return $this->crudAction($entity, AdminCrudEvent::DELETE);
            }
        }

        return $this->render(
            $this->getEditTemplate(),
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));
    }

    /**
     * Generic new controller helper.
     *
     * @param $entity
     * @param Request $request
     *
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function manageNew($entity, Request $request, FormTypeInterface $form)
    {
        $form = $this->createForm($form, $entity, array(
            'action' => $this->getNewUrl($entity),
            'method' => 'POST',
        ));

        $form
            ->add('create', 'submit', array('attr' => array('class' => 'btn btn-primary')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->crudAction($entity, AdminCrudEvent::CREATE);
        }

        return $this->render(
            $this->getNewTemplate(),
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));
    }

    /**
     * Manage full action: send events, manage entity, send flash messages.
     *
     * @param $entity
     * @param $action
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function crudAction($entity, $action)
    {
        // Before, send preAction
        $event = $this->dispatchCrudEventPreAction($entity, $action);
        $em = $this->getDoctrine()->getManager();

        // Update entity according to action (can be changed in event)
        switch ($event->getAction()) {
            case AdminCrudEvent::CREATE;
                $em->persist($entity);
                break;
            case AdminCrudEvent::DELETE;
                $em->remove($entity);
                break;
        }
        if ($action > AdminCrudEvent::DONT_TOUCH) {
            $em->flush();
        }

        // Send postAction Event
        $event = $this->dispatchCrudEventPostAction($entity, $action);

        switch ($event->getAction()) {
            case AdminCrudEvent::CREATE;
                $this->addFlashMessage('success', $this->getFlashCreatedMessage());
                break;
            case AdminCrudEvent::DELETE;
                $this->addFlashMessage('success', $this->getFlashDeletedMessage());

                return $this->redirect($this->getListUrl());
                break;
            case AdminCrudEvent::UPDATE;
                $this->addFlashMessage('success', $this->getFlashSavedMessage());
                break;

        }

        return $this->redirect($this->getShowUrl($entity));
    }

    /**
     * Dispatch Event before action.
     *
     * @param $entity
     * @param $action
     *
     * @return AdminCrudEvent
     */
    protected function dispatchCrudEventPreAction($entity, $action)
    {
        return $this->dispatchCrudEvent($entity, $action, 'sbo.crud.preAction');
    }

    /**
     * Dispatch Event after action.
     *
     * @param $entity
     * @param $action
     *
     * @return AdminCrudEvent
     */
    protected function dispatchCrudEventPostAction($entity, $action)
    {
        return $this->dispatchCrudEvent($entity, $action, 'sbo.crud.postAction');
    }

    /**
     * Generic Event dispatcher.
     *
     * @param $entity
     * @param $action
     * @param $eventName
     *
     * @return AdminCrudEvent
     */
    protected function dispatchCrudEvent($entity, $action, $eventName)
    {
        $event = new AdminCrudEvent($entity, $action, $this->getUser());
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return $event;
    }

    /**
     * Dispatch Event before action.
     *
     * @param $entity
     * @param $action
     *
     * @return AdminAssociationActionEvent
     */
    protected function dispatchAssociationActionEventPreAction($entity, $action, $target)
    {
        return $this->dispatchAssociationActionEvent($entity, $action, $target, 'sbo.association.preAction');
    }

    /**
     * Dispatch Event after action.
     *
     * @param $entity
     * @param $action
     *
     * @return AdminAssociationActionEvent
     */
    protected function dispatchAssociationActionEventPostAction($entity, $action, $target)
    {
        return $this->dispatchAssociationActionEvent($entity, $action, $target, 'sbo.association.postAction');
    }

    /**
     * Generic Association Action dispatcher.
     *
     * @param $entity
     * @param $action
     * @param $target
     * @param $eventName
     *
     * @return AdminAssociationActionEvent
     */
    protected function dispatchAssociationActionEvent($entity, $action, $target, $eventName)
    {
        $event = new AdminAssociationActionEvent($entity, $action, $target, $this->getUser());
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return $event;
    }

    /**
     * Generic show controller helper.
     *
     * @param $entity
     *
     * @return Response
     */
    protected function manageShow($entity)
    {
        return $this->render($this->getShowTemplate(), array(
            'entity' => $entity,
        ));
    }

    /**
     * Generic index controller helper.
     *
     * @return Response
     */
    protected function manageIndex()
    {
        $postDatatable = $this->get($this->route_name.'_datatable');
        $postDatatable->buildDatatable();

        return $this->render($this->getIndexTemplate(), array(
            'datatable' => $postDatatable,
        ));
    }

    /**
     * Generic index controller helper.
     *
     * @param $entity
     * @param $field
     *
     * @return Response
     *
     * @throws \Exception
     */
    protected function manageFieldIndex($entity, $field)
    {
        if ($entity == null || $field == null || $this->has($this->route_name.'_'.$field.'_datatable') == false) {
            throw new \Exception('All the parameters are not correctly set');
        }
        $postDatatable = $this->get($this->route_name.'_'.$field.'_datatable');
        $postDatatable->buildDatatable(['entity' => $entity]);

        return $this->render($this->getIndexTemplate(strtolower($field)), array(
            'datatable' => $postDatatable,
            'entity' => $entity,
        ));
    }

    /**
     * @return string
     */
    protected function getIndexTemplate($field = null)
    {
        if ($field != null) {
            return $this->bundle_name.':Admin/'.$this->entity_name.'/'.$field.':'.'index.html.twig';
        }

        return $this->bundle_name.':Admin/'.$this->entity_name.':'.'index.html.twig';
    }

    /**
     * @return Response
     */
    protected function manageDatatableJson()
    {
        $postDatatable = $this->get($this->route_name.'_datatable');
        $postDatatable->buildDatatable();
        $datatable = $this->get('sg_datatables.query')->getQueryFrom($postDatatable);

        return $datatable->getResponse();
    }

    /**
     * @param $entity
     * @param $field
     * @param $reversedField
     *
     * @return Response
     *
     * @throws \Exception
     */
    protected function manageFieldDatatableJson($entity, $field, $reversedField, $type = 'one')
    {
        if ($entity == null || $field == null || $this->has($this->route_name.'_'.$field.'_datatable') == false) {
            throw new \Exception('All the parameters are not correctly set');
        }

        $postDatatable = $this->get($this->route_name.'_'.$field.'_datatable');
        $postDatatable->buildDatatable(['entity' => $entity]);

        if (method_exists($postDatatable, 'addLineFormatter') && method_exists($entity, 'getId')) {
            $postDatatable->addLineFormatter(function ($ligne) use ($entity) {
                $ligne['entity_id'] = $entity->getId();

                return $ligne;
            });
        }

        $datatable = $this->get('sg_datatables.query')->getQueryFrom($postDatatable);

        $entityName = $this->entity_name;

        if ($type === 'one') {
            $datatable->addWhereAll(function (QueryBuilder $qb) use ($entity, $entityName, $reversedField) {
                $qb
                    ->andWhere($qb->getRootAliases()[0].'.'.$reversedField." = :$entityName")
                    ->setParameter("$entityName", $entity)
                ;
            });
        } elseif ($type === 'many') {
            $datatable->addWhereAll(function (QueryBuilder $qb) use ($entity, $entityName, $reversedField) {
                $qb
                    ->join($qb->getRootAliases()[0].'.'.$reversedField, 'reverseField')
                    ->andWhere('reverseField.id = :'.$entityName.'_id')
                    ->setParameter($entityName.'_id', $entity->getId())
                ;
            });
        }

        return $datatable->getResponse();
    }

    /**
     * @param $type
     * @param $message
     */
    protected function addFlashMessage($type, $message)
    {
        $this->addFlash(
            $type,
            $this->get('translator')->trans($message, [], 'admin')
        );
    }

    /**
     * @param Request $request
     * @param $class
     * @param closure|string $filerFunction
     * @param closure|null   $renderResult
     *
     * @return JsonResponse
     */
    protected function searchSelect2(Request $request, $class, $fieldSearchFunction = 'title', $renderResult = null)
    {
        // 3rd parameter: use the following code to have a more flexible search
        // $querySearch = function(\Doctrine\ORM\QueryBuilder $queryBuilder, $query) {
        //     $queryBuilder
        //          ->andWhere("o.{{ property }} LIKE :{{ property }}")
        //          ->setParameter("{{ property }}","%$query%");
        //  };

        // 4th parameter: use the following code to customize rendering of select2
        // $twig = $this->get('twig');
        // $html = $this->bundle_name.":Admin/".strtolower(substr($class,strripos($class,'\\')+1)).":renderResultSelect2.html.twig";
        // $appendResult = function( $entity, $query) use ($twig, $html) {
        //     return [
        //         'renderValue'    => $twig->render($html, ['entity' => $entity, 'query' => $query]),
        //          'text'          => $entity->__toString(),
        //          'id'            => $entity->getId()
        //      ];
        //  };

        $query = $request->get('q', null);
        $limit = (int) $request->get('page_limit', 10);
        $page = (int) $request->get('page', 1);

        $res = ['term' => $query, 'more' => false, 'results' => []];

        // creation du queryBulider
        $queryBuilder = $this->get('doctrine.orm.entity_manager')
            ->getRepository($class)
            ->createQueryBuilder('o')
        ;

        // add filters
        if (is_string($fieldSearchFunction)) {
            $queryBuilder
                ->andWhere("o.$fieldSearchFunction LIKE :$fieldSearchFunction")
                ->setParameter("$fieldSearchFunction", "%$query%")
                ->orderBy("o.$fieldSearchFunction")
            ;
        } elseif (is_callable($fieldSearchFunction)) {
            $fieldSearchFunction($queryBuilder, $query);
        }

        // page & limit
        if (is_int($page) && $page > 0 &&  is_int($limit) && $limit > 0) {
            $queryBuilder = $queryBuilder
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit + 1)
            ;
        }

        // get results
        $result = $queryBuilder
            ->getQuery()
            ->getResult()
        ;

        if ($renderResult == null) {
            $getter = is_string($fieldSearchFunction) ? $this->getGetter($fieldSearchFunction) : '__toString';
            $renderResult = function ($entity, $query) use ($getter) {
                return [
                    'text' => $entity->$getter(),
                    'id' => $entity->getId(),
                ];
            };
        }

        // create response
        foreach ($result as $object) {
            $res['results'][] = $renderResult($object, $query);
        }

        // check if not more result
        if (count($result) > $limit) {
            $res['more'] = true;
            $res['results'] = array_slice($res['results'], 0, $limit);
        }

        $response = new JsonResponse($res);
        $response->setCallback($request->get('callback'));

        // send data into JSON
        return $response;
    }

    /**
     * Manage search to add in datatable.
     *
     * @param Request $request
     * @param $entity
     * @param $fieldClass
     * @param $field
     * @param $fieldSearch
     *
     * @return JsonResponse
     */
    protected function manageSearchFieldMany(Request $request, $entity, $fieldClass, $field, $fieldSearch)
    {
        $twig = $this->get('twig');

        $getTitle = $this->getGetter($fieldSearch);

        $querySearch = function (\Doctrine\ORM\QueryBuilder $queryBuilder, $query) use ($entity, $field, $fieldSearch, $fieldClass) {

            $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder();
            $subQuery
                ->select("field.id")
                ->from(get_class($entity), 'entity')
                ->join('entity.'.$field, 'field')
                ->where("entity.id = :entity_id")
                ->setParameter('entity_id', $entity->getId())
            ;

            $queryBuilder
                ->andWhere($queryBuilder->expr()->notIn('o.id',$subQuery->getDql()))
                ->setParameter('entity_id', $entity->getId())
                ->andWhere('o.'.$fieldSearch.' LIKE :'.$fieldSearch)
                ->setParameter($fieldSearch, "%$query%")
                ->orderBy('o.'.$fieldSearch)
            ;

        };

        $fieldClassName = substr($fieldClass, strripos($fieldClass, '\\') + 1);
        if ($this->get('templating')->exists($this->bundle_name.':Admin/'.$fieldClassName.':'.'renderResultSelect2.html.twig')) {
            $appendResult = function ($subentity, $query) use ($entity, $field, $twig, $getTitle, $fieldClassName) {
                return [
                    'renderValue' => $twig->render($this->bundle_name.':Admin/'.$fieldClassName.':'.'renderResultSelect2.html.twig', ['entity' => $subentity, 'query' => $query]),
                    'confirme' => $this->generateUrl($this->route_name.'_'.strtolower($field).'_add', ['id' => $entity->getId(), strtolower($fieldClassName).'_id' => $subentity->getId()]),
                    'text' => $subentity->$getTitle(),
                    'id' => $subentity->getId(),
                ];
            };
        } else {
            $appendResult = function ($subentity, $query) use ($entity, $field, $twig, $getTitle, $fieldClassName) {
                return [
                    'confirme' => $this->generateUrl($this->route_name.'_'.strtolower($field).'_add', ['id' => $entity->getId(), strtolower($fieldClassName).'_id' => $subentity->getId()]),
                    'text' => $subentity->$getTitle(),
                    'id' => $subentity->getId(),
                ];
            };
        }

        return $this->searchSelect2($request, $fieldClass, $querySearch, $appendResult);
    }

    /**
     * @param $source
     * @param $target
     * @param $field
     * @param $action
     * @param bool $contains
     *
     * @return JsonResponse
     */
    protected function manageJsonAction($source, $target, $field, $action, $contains = true)
    {
        $res = ['result' => true, 'message' => ''];

        $getter = $this->getGetter($field);

        if ($source->$getter()->contains($target) == $contains) {
            $event = $this->dispatchAssociationActionEventPreAction($source, $action, $target);

            if ($event->getAction() != AdminAssociationActionEvent::DONT_TOUCH) {
                $source2 = $event->getItem();
                $target2 = $event->getTarget();
                $action2 = $event->getAction();

                $source2->$action2($target2);
                $this->get('doctrine.orm.entity_manager')->flush();
            }
            //$res['html'] = $this->get("twig")->render("SedonaSBOTestBundle:Admin/Artist:_renderAlbum.html.twig", ['object'=>$target]);

            $event = $this->dispatchAssociationActionEventPostAction($source, $action, $target);
        }

        return new JsonResponse($res);
    }

    /**
     * @param $field
     *
     * @return string
     */
    protected function getGetter($field)
    {
        return 'get'.ucfirst($field);
    }
}
