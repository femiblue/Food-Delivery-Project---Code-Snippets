<?php
/**
 * Created by Php Designer.
 * User: femiblue
 * Date: 26/12/2016
 * Time: 02:26 PM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\Specialoffers;
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

class SpecialoffersController extends FOSRestController
{
    /**
     * List all Special Offers.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing Special Offers.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many Special Offers to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Specialoffers:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getSpecialoffersAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $current = $start;


        //$specialoffers = $this->getDoctrine()->getRepository('SuRestaurantBundle:SpecialOffers')->findBy(array(),array(),$limit,$start);

        $specialoffers   = $this->container->get('su_restaurant.specialoffers.handler')->all($limit,$start);

        return array('entity'=>$specialoffers);

    }


    /**
     * Get a single Special Offer.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:Specialoffers:show.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the dish id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getSpecialofferAction($id)
    {
        $specialOffers = $this->getOr404($id);
        //entity
        return array('entity'=>$specialOffers);
    }

    /**
     * Creates a new Special Offer from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\SpecialoffersType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:Specialoffers:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     * //@Security("has_role('ROLE_USER')")
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postSpecialoffersAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.specialoffers.handler')->post(
                $request->request->all()
            );
        $routeOptions = array(
            'id' => $newPage->getDishId(),
            '_format' => $request->get('_format')
        );
        return $this->routeRedirectView('specialoffers', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Special Offer from the submitted data or create a new Special Offer at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\SpecialoffersType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Specialoffers:edit.html.twig",
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
    public function putSpecialoffersAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.specialoffers.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.specialoffers.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.specialoffers.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getSpecialoffersId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('specialoffers', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing SpecialOffers from the submitted data or create a new Special Offers at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\SpecialoffersType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Specialoffers:editPage.html.twig",
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
    public function patchSpecialofferAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.specialoffers.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getSpecialoffersId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('specialoffers', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return Specialoffers
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.specialoffers.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}