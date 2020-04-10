<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 26/09/2016
 * Time: 06:17 AM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\Dish;
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

class DishController extends FOSRestController
{
    /**
     * List all Dish Dishes.
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
     * @Annotations\View(template = "SuRestaurantBundle:Dish:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getDishesAction(ParamFetcherInterface $paramFetcher)
    {
        $counter     = 0;
        $dish_arr    = array();
        $offset      = $paramFetcher->get('offset');
        $start       = null == $offset ? 0 : $offset + 1;
        $limit       = $paramFetcher->get('limit');

        $current = $start;


        //$dish = $this->getDoctrine()->getRepository('SuRestaurantBundle:Dish')->findBy(array(),array(),$limit,$start);

        $dish   = $this->container->get('su_restaurant.dish.handler')->all($limit,$start);
        //print_r($dish);
        foreach($dish as $dish_val){
            $dish_arr[$counter] = $dish_val;
            $counter++;
        }
        //return $dish;
        return array('entity'=>$dish_arr);

    }


    /**
     * Get a single dish dish.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:Dish:show.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the dish id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getDishAction($id)
    {
        $dish = $this->getOr404($id);
        //entity
        return array('entity'=>$dish);
    }
    
    
    /**
     * List all Dishes By Specific Category.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing dish by categories.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many dish by categories to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Dish:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service 
     * @param int $catid the  	category id
     *
     * @return array
     */
    public function getDishesbycatAction(ParamFetcherInterface $paramFetcher,$catid)
    {   
        $counter     = 0;
        $dish_by_cat = array();
        $offset      = $paramFetcher->get('offset');
        $start       = null == $offset ? 0 : $offset + 1;
        $limit       = $paramFetcher->get('limit');

        $current = $start;
        
        //echo "Categor Id..".$catid;
        //echo "<br/>";
        //$dish = $this->getDoctrine()->getRepository('SuRestaurantBundle:Dish')->findBy(array(),array(),$limit,$start);

        $dish   = $this->container->get('su_restaurant.dish.handler')->all($limit,$start);
        //print_r($dish[0]->getCategory());die();
        //pick only ones that belong to the category
        foreach($dish as $dish_val){
            if($catid == $dish_val->getCategory())
            {
                $dish_by_cat[$counter] = $dish_val;
                $counter++;
            }
            
        }
        //print_r($dish_by_cat); die();
        return array('entity'=>$dish_by_cat);

    }
    
     /**
     * List all Dishes By Specific Shop.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing dish by categories.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many dish by categories to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:Dish:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service 
     * @param int $shopid the  	shop id
     *
     * @return array
     */
    public function getDishesbyshopAction(ParamFetcherInterface $paramFetcher,$shopid)
    {   
        $counter     = 0;
        $dish_by_shop= array();
        $offset      = $paramFetcher->get('offset');
        $start       = null == $offset ? 0 : $offset + 1;
        $limit       = $paramFetcher->get('limit');

        $current = $start;
        
        //echo "Shop Id..".$shopid;
        //echo "<br/>";
        //$dish = $this->getDoctrine()->getRepository('SuRestaurantBundle:Dish')->findBy(array(),array(),$limit,$start);

        $dish   = $this->container->get('su_restaurant.dish.handler')->all($limit,$start);
        //print_r($dish[0]->getCategory());die();
        //pick only ones that belong to the category
        foreach($dish as $dish_val){
            if($shopid == $dish_val->getShop())
            {
                $dish_by_shop[$counter] = $dish_val;
                $counter++;
            }
            
        }
        //print_r($dish_by_shop); die();
        return array('entity'=>$dish_by_shop);

    }

    
    

    /**
     * Creates a new dish dish from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DishType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:Dish:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     * //@Security("has_role('ROLE_ANONYMOUS',ROLE_USER')")
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postDishAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.dish.handler')->post(
                $request->request->all()
            );
        $routeOptions = array(
            'id' => $newPage->getDishId(),
            '_format' => $request->get('_format')
        );
        return $this->routeRedirectView('get_categories', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Dish from the submitted data or create a new Dish at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DishType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Dish:edit.html.twig",
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
    public function putDishAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.dish.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.dish.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.dish.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getDishId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('get_categories', $routeOptions, $statusCode);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing Dish from the submitted data or create a new dish at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\DishType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:Dish:editPage.html.twig",
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
    public function patchDishAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.dish.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getDishId(),
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
     * @return Dish
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('su_restaurant.dish.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}