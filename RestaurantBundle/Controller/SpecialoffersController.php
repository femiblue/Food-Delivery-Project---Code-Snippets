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

use Su\RestaurantBundle\Entity\Specialoffers;
use Su\RestaurantBundle\Form\SpecialoffersType;
use Su\RestaurantBundle\Form\SpecialoffersFilterType;

/**
 * Special Offers controller.
 *
 * @Route("/specialoffers")
 */
class SpecialoffersController extends Controller
{
    /**
     * Lists all Special Offers entities.
     *
     * @Route("/", name="specialoffers")
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
        $filterForm = $this->createForm(new DishFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('SuRestaurantBundle:Specialoffers')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('SpecialoffersControllerFilter');
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
                $session->set('SpecialoffersControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('SpecialoffersControllerFilter')) {
                $filterData = $session->get('SpecialoffersControllerFilter');
                $filterForm = $this->createForm(new SpecialoffersFilterType(), $filterData);
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
            return $me->generateUrl('specialoffers', array('page' => $page));
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
     * Creates a new Special Offers entity.
     *
     * @Route("/", name="specialoffers_create")
     * @Method("POST")
     * @Template("SuRestaurantBundle:Specialoffers:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Specialoffers();
        $form = $this->createForm(new SpecialoffersType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            //
            $d_dish = $entity->getDish();
            $entity->setCategory($d_dish->getDishId());
			
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            //return $this->redirect($this->generateUrl('special_offers_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Special Offers entity.
     *
     * @Route("/new", name="specialoffers_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SpecialOffers();
        $form   = $this->createForm(new SpecialoffersType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Special Offers entity.
     *
     * @Route("/{id}", name="specialoffers_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:Specialoffers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Special Offers entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Special Offers entity.
     *
     * @Route("/{id}/edit", name="specialoffers_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:Specialoffers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Special Offers entity.');
        }

        $editForm = $this->createForm(new SpecialoffersType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Special Offers entity.
     *
     * @Route("/{id}", name="specialoffers_update")
     * @Method("PUT")
     * @Template("SuRestaurantBundle:Specialoffers:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SuRestaurantBundle:Specialoffers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Special Offers entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new SpecialoffersType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            //
            $d_dish = $entity->getDish();
            $entity->setDish($d_dish->getDishId());


            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            //return $this->redirect($this->generateUrl('special_offers_edit', array('id' => $id)));
			return $this->redirect($this->generateUrl('special'));
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
     * Deletes a Special Offers entity.
     *
     * @Route("/{id}", name="specialoffers_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SuRestaurantBundle:Specialoffers')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Special Offers entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('special'));
    }

    /**
     * Creates a form to delete a Special Offers entity by id.
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
