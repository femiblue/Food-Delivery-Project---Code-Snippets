<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 26/09/2016
 * Time: 06:17 AM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\OrderDish;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Su\RestaurantBundle\Exception\InvalidFormException;

class OrderDishController extends FOSRestController
{
    /**
     * List all Order Dishes.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing order dish.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many order order_dish to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:OrderDish:index.html.twig")
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getOrderdishesAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $current = $start;


        //$order_dish = $this->getDoctrine()->getRepository('SuRestaurantBundle:OrderDish')->findBy(array(),array(),$limit,$start);

        $order_dish   = $this->container->get('su_restaurant.orderdish.handler')->all($limit,$start);

        return array('entity'=>$order_dish);

    }


    /**
     * Get a single order_dish.
     *
     * @ApiDoc(
     *   output = "Su\RestaurantBundle\Entity\OrderDish",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:OrderDish:show.html.twig")
     *
     * @param int $id the order_dish id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getOrderdishAction($id)
    {
        $order_dish = $this->getOr404($id);
        //entity
        return array('entity'=>$order_dish);
    }

    /**
     * Creates a new order_dish order_dish from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\OrderDishType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:OrderDish:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postOrderdishAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.orderdish.handler')->post(
                $request->request->all()
            );
        $statusCode = Response::HTTP_CREATED;
        $routeOptions = array(
            'id' => $newPage->getId(),
            '_format' => $request->get('_format')
        );
        return $statusCode;
        //return $this->routeRedirectView('get_order_dishes', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing OrderDish from the submitted data or create a new OrderDish at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\OrderDishType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:OrderDish:edit.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function putOrderdishAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.orderdish.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.orderdish.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.orderdish.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );
            return $statusCode;
            //return $this->routeRedirectView('get_categories', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing OrderDish from the submitted data or create a new order_dish at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\OrderDishType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:OrderDish:editPage.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function patchOrderdishAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.orderdish.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_order_dishes ', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return OrderDish
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.orderdish.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}