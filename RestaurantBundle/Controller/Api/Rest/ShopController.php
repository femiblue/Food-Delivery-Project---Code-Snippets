<?php
/**
 * Created by Php Designer.
 * User: femiblue
 * Date: 20/12/2016
 * Time: 01:11 PM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\Shop;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

class ShopController extends FOSRestController
{
    /**
     * List all Shops.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing shops.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many shops to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Shop:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getShopsAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        //$limit = $paramFetcher->get('limit');
        $limit = 2147483647; //int's max range
        $current = $start;


        //$shop = $this->getDoctrine()->getRepository('SuRestaurantBundle:Shop')->findBy(array(),array(),$limit,$start);

        $shop   = $this->container->get('su_restaurant.shop.handler')->all($limit,$start);

        return array('entity'=>$shop);

    }


    /**
     * Get a single shop.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:Shop:show.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the shop id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getShopAction($id)
    {
        $shop = $this->getOr404($id);
        //entity
        return array('entity'=>$shop);
    }

    /**
     * Creates a new shop from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ShopType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:Shop:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postShopAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.shop.handler')->post(
                $request->request->all()
            );
        $routeOptions = array(
            'id' => $newPage->getShopId(),
            '_format' => $request->get('_format')
        );
        return $this->routeRedirectView('get_shops', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Shop from the submitted data or create a new Shop at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ShopType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Shop:edit.html.twig",
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
    public function putShopAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.shop.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.shop.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.shop.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getShopId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_shops', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing Shop from the submitted data or create a new shop at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ShopType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Shop:editPage.html.twig",
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
    public function patchShopAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.shop.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getShopId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_shops', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return Shop
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.shop.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}