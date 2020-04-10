<?php

namespace Su\RestaurantBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Su\RestaurantBundle\Entity\DeliveryOrder;
use Su\RestaurantBundle\Form\DeliveryOrderType;
use Su\RestaurantBundle\Form\DeliveryOrderFilterType;

/**
 * DeliveryOrder controller.
 *
 * @Route("/deliveryorder")
 */
class DeliveryOrderController extends Controller
{
    /**
     * Lists all DeliveryOrder entities.
     *
     * @Route("/", name="deliveryorder")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        );
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new DeliveryOrderFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('SuRestaurantBundle:DeliveryOrder')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('DeliveryOrderControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('DeliveryOrderControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('DeliveryOrderControllerFilter')) {
                $filterData = $session->get('DeliveryOrderControllerFilter');
                $filterForm = $this->createForm(new DeliveryOrderFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('deliveryorder', array('page' => $page));
        };

        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Creates a new DeliveryOrder entity.
     *
     * @Route("/", name="deliveryorder_create")
     * @Method("POST")
     * @Template("SuRestaurantBundle:DeliveryOrder:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new DeliveryOrder();
        $form = $this->createForm(new DeliveryOrderType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $corderid  = $entity->getClientOrderId();
            $entity->setClientOrderId($corderid->getId());
            
            $addressid = $entity->getAddressId();
            $entity->setAddressId($addressid->getId());
            
            $user = $this->getUser();
            $clientid  = $entity->getClientId();
            if($user){
              $entity->setClientId($user->getId());
            }else{
              $entity->setClientId("NULL"); //set to null if user not logged in
            }
            
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('deliveryorder_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new DeliveryOrder entity.
     *
     * @Route("/new", name="deliveryorder_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new DeliveryOrder();
        $form   = $this->createForm(new DeliveryOrderType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a DeliveryOrder entity.
     *
     * @Route("/{id}", name="deliveryorder_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:DeliveryOrder')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DeliveryOrder entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing DeliveryOrder entity.
     *
     * @Route("/{id}/edit", name="deliveryorder_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:DeliveryOrder')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DeliveryOrder entity.');
        }

        $editForm = $this->createForm(new DeliveryOrderType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing DeliveryOrder entity.
     *
     * @Route("/{id}", name="deliveryorder_update")
     * @Method("PUT")
     * @Template("SuRestaurantBundle:DeliveryOrder:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:DeliveryOrder')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DeliveryOrder entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new DeliveryOrderType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $corderid  = $entity->getClientOrderId();
            $entity->setClientOrderId($corderid->getId());
            
            $addressid = $entity->getAddressId();
            $entity->setAddressId($addressid->getId());
            
            $user = $this->getUser();
            $clientid  = $entity->getClientId();
            if($user){
              $entity->setClientId($clientid->$user->getId());
            }else{
              $entity->setClientId("NULL"); //set to null if user not logged in
            }
            
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('deliveryorder_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a DeliveryOrder entity.
     *
     * @Route("/{id}", name="deliveryorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SuRestaurantBundle:DeliveryOrder')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DeliveryOrder entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('deliveryorder'));
    }

    /**
     * Creates a form to delete a DeliveryOrder entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
