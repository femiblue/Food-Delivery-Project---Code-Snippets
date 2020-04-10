<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 26/09/2016
 * Time: 06:17 AM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\Category;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Su\RestaurantBundle\Exception\InvalidFormException;

class CategoryController extends FOSRestController
{
    /**
     * List all Categories.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing dish categories.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many dish categories to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Category:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *
     * @return array
     */
    public function getCategoriesAction(ParamFetcherInterface $paramFetcher)
    { 
        /*
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        */
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $current = $start;


        //$category = $this->getDoctrine()->getRepository('SuRestaurantBundle:Category')->findBy(array(),array(),$limit,$start);

        $category   = $this->container->get('su_restaurant.category.handler')->all($limit,$start);

        return array('entity'=>$category);

    }


    /**
     * Get a single category.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:Category:show.html.twig")
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the category id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getCategoryAction($id)
    {
        $category = $this->getOr404($id);
        //entity
        return array('entity'=>$category);
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('SuRestaurantBundle:Category')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Category entity.');
//        }
//
//        //$deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'category'      => $entity,
//            //'delete_form' => $deleteForm->createView(),
//        );

        //return array('category'=>$category);
    }

    /**
     * Creates a new dish category from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\CategoryType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:Category:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * /@Security("has_role('ROLE_USER')")
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postCategoryAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.category.handler')->post(
                $request->request->all()
            );
        $routeOptions = array(
            'id' => $newPage->getCategoryId(),
            '_format' => $request->get('_format')
        );
        return $this->routeRedirectView('get_categories', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Category from the submitted data or create a new Category at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\CategoryType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Category:edit.html.twig",
     *  templateVar = "form"
     * )
     * //@Security("has_role('ROLE_USER')")
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function putCategoryAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.category.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.category.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.category.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getCategoryId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_categories', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing Category from the submitted data or create a new category at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\CategoryType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Category:editPage.html.twig",
     *  templateVar = "form"
     * )
     *
     * //@Security("has_role('ROLE_USER')")
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function patchCategoryAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.category.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getCategoryId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_categories', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return PageInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.category.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}