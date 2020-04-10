<?php
/**
 * Created by PHP Designer.
 * User: femiblue
 * Date: 02/02/2017
 * Time: 01:06 AM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\DeliveryOrder;
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

class DeliveryOrderController extends FOSRestController
{
    /**
     * List all Delivery Orders.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing delivery orders.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many order delivery_order to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:DeliveryOrder:index.html.twig")
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getDeliveryordersAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $current = $start;


        $order_dish   = $this->container->get('su_restaurant.delivery.handler')->all($limit,$start);

        return array('entity'=>$order_dish);

    }


    /**
     * Get a single delivery order.
     *
     * @ApiDoc(
     *   output = "Su\RestaurantBundle\Entity\DeliveryOrder",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:DeliveryOrder:show.html.twig")
     *
     * @param int $id the delivery_order id
     *
     * @return array
     * //@Security("has_role('ROLE_USER')")
     * @throws NotFoundHttpException when note not exist
     */
    public function getDeliveryorderAction($id)
    {
        $order_dish = $this->getOr404($id);
        //entity
        return array('entity'=>$order_dish);
    }

    /**
     * Creates a new delivery_order from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DeliveryOrderType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:DeliveryOrder:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     * * //@Security("has_role('ROLE_USER')")
     */
    public function postDeliveryorderAction(Request $request)
    { 
        try {
            $newPage = $this->container->get('su_restaurant.delivery.handler')->post(
                $request->request->all(),
                'DeliveryOrder'
            );
        $statusCode = Response::HTTP_CREATED;
        $routeOptions = array(
            'id' => $newPage->getId(),
            '_format' => $request->get('_format')
        );
        return $statusCode;
        //return $this->routeRedirectView('get_deliveryorders', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Delivery Order from the submitted data or create a new Delivery Order at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DeliveryOrderType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:DeliveryOrder:edit.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     * //@Security("has_role('ROLE_USER')")
     * @throws NotFoundHttpException when page not exist
     */
    public function putDeliveryorderAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.delivery.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.delivery.handler')->post(
                    $request->request->all(),
                    'DeliveryOrder'
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.delivery.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );
            return $statusCode;
            //return $this->routeRedirectView('get_deliveryorders', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing DeliveryOrder from the submitted data or create a new order_dish at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DeliveryOrderType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:DeliveryOrder:editPage.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     * //@Security("has_role('ROLE_USER')")
     * @throws NotFoundHttpException when page not exist
     */
    public function patchDeliveryorderAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.delivery.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_deliveryorder ', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return DeliveryOrder
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.delivery.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}