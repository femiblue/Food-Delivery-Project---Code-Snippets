<?php

namespace Su\RestaurantBundle\Controller;

//use Su\RestaurantBundle\Entity\ClientAddress;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Su\RestaurantBundle\Entity\OrderDish;
use Su\RestaurantBundle\Form\OrderDishType;
//use Su\RestaurantBundle\Form\clientAddressType;
use Su\RestaurantBundle\Form\OrderDishFilterType;

/**
 * OrderDish controller.
 *
 * @Route("/orderdish") 
 */
class OrderDishController extends Controller
{
    /**
     * Lists all OrderDish entities.
     *
     * @Route("/", name="orderdish")
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
        $filterForm = $this->createForm(new OrderDishFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('SuRestaurantBundle:OrderDish')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('OrderDishControllerFilter');
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
                $session->set('OrderDishControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('OrderDishControllerFilter')) {
                $filterData = $session->get('OrderDishControllerFilter');
                $filterForm = $this->createForm(new OrderDishFilterType(), $filterData);
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
            return $me->generateUrl('orderdish', array('page' => $page));
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
     * Creates a new OrderDish entity.
     *
     * @Route("/", name="orderdish_create")
     * @Method("POST")
     * @Template("SuRestaurantBundle:OrderDish:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new OrderDish();
        //$client_add = new ClientAddress();
        $form = $this->createForm(new OrderDishType(), $entity);
        //$form2 = $this->createForm(new ClientAddressType(), $client_add);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $d_clientaddress = $entity->getClientAddress();
            $entity->setClientAddress($d_clientaddress->getId());

            $em->persist($entity);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('orderdish_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new OrderDish entity.
     *
     * @Route("/new", name="orderdish_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new OrderDish();
        $form   = $this->createForm(new OrderDishType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a OrderDish entity.
     *
     * @Route("/{id}", name="orderdish_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:OrderDish')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderDish entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OrderDish entity.
     *
     * @Route("/{id}/edit", name="orderdish_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:OrderDish')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderDish entity.');
        }

        $editForm = $this->createForm(new OrderDishType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing OrderDish entity.
     *
     * @Route("/{id}", name="orderdish_update")
     * @Method("PUT")
     * @Template("SuRestaurantBundle:OrderDish:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:OrderDish')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderDish entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new OrderDishType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            
            $d_clientaddress = $entity->getClientAddress();
            $entity->setClientAddress($d_clientaddress->getId());
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('orderdish_edit', array('id' => $id)));
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
     * Deletes a OrderDish entity.
     *
     * @Route("/{id}", name="orderdish_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SuRestaurantBundle:OrderDish')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrderDish entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('orderdish'));
    }

    /**
     * Creates a form to delete a OrderDish entity by id.
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
