<?php
/**
 * Created by Notepad++.
 * User: femiblue
 * Date: 21/12/2016
 * Time: 04:06 PM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\Favorites;
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

class FavoritesController extends FOSRestController
{
    /**
     * List all Favorites.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing favorites.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many favorites to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Favorites:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getFavoritesAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $current = $start;


        //$favorites = $this->getDoctrine()->getRepository('SuRestaurantBundle:Favorites')->findBy(array(),array(),$limit,$start);

        $favorites   = $this->container->get('su_restaurant.favorites.handler')->all($limit,$start);

        return array('entity'=>$favorites);

    }


    /**
     * Get a single favorites.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:Favorites:show.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the favorites id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getFavoriteAction($id)
    {
        $favorites = $this->getOr404($id);
        //entity
        return array('entity'=>$favorites);
    }

    /**
     * Creates a new favorites from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\FavoritesType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:Favorites:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     * //@Security("has_role('ROLE_USER')")
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postFavoritesAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.favorites.handler')->post(
                $request->request->all()
            );
        $routeOptions = array(
            'id' => $newPage->getFavoritesId(),
            '_format' => $request->get('_format')
        );
        return $this->routeRedirectView('get_favorites', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Favorites from the submitted data or create a new Favorites at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\FavoritesType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Favorites:edit.html.twig",
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
    public function putFavoritesAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.favorites.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.favorites.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.favorites.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getFavoritesId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_favorites', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing Favorites from the submitted data or create a new Favorites at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\FavoritesType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Favorites:editPage.html.twig",
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
    public function patchFavoritesAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.favorites.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getFavoritesId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_favorites', $routeOptions, Response::HTTP_NO_CONTENT);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return Favorites
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.favorites.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}