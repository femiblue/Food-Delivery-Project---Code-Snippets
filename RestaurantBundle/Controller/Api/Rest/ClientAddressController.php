<?php
/**
 * Created by PHP Designer.
 * User: femiblue
 * Date: 28/12/2016
 * Time: 05:13 PM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;



use Su\RestaurantBundle\Entity\ClientAddress;
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

class ClientAddressController extends FOSRestController
{
    /**
     * List all Client Address.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing client addresses.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many client address to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:ClientAddress:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *
     * @return array
     */
    public function getAllclientaddressAction(ParamFetcherInterface $paramFetcher)
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


        //$category = $this->getDoctrine()->getRepository('SuRestaurantBundle:ClientAddress')->findBy(array(),array(),$limit,$start);

        $clientaddress   = $this->container->get('su_restaurant.clientaddress.handler')->all($limit,$start);

        return array('entity'=>$clientaddress);

    }


    /**
     * Get a single client addresses.
     *
     * @ApiDoc(
     *   output = "AppBundle\Model\Note",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @Annotations\View( template = "SuRestaurantBundle:ClientAddress:show.html.twig")
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param int $id the clientAddress id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getClientaddressAction($id)
    {
        $clientAddress = $this->getOr404($id);
        //entity
        return array('entity'=>$clientAddress);
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
     * List all Client Addresses By Specific Client.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing addresses by client.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many addresses by client to return.")
     *
     * @Annotations\View(template = "SuRestaurantBundle:ClientAddress:index.html.twig")
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     * @param ParamFetcherInterface $paramFetcher param fetcher service 
     * @param int $clientid the  	client id
     *
     * @return array
     */
    public function getClientaddressbyclientAction(ParamFetcherInterface $paramFetcher,$clientid)
    {  
        $counter       = 0;
        $addy_by_client= array();
        $offset        = $paramFetcher->get('offset');
        $start         = null == $offset ? 0 : $offset + 1;
        //$limit         = $paramFetcher->get('limit');
        $limit         = 2147483647; //int's max range
        
        $current       = $start;
        
        $clientaddress = $this->container->get('su_restaurant.clientaddress.handler')->all($limit,$start);
        
        foreach($clientaddress as $clientaddress_val){
            if($clientid == $clientaddress_val->getClientId())
            {
                $addy_by_client[$counter] = $clientaddress_val;
                $counter++;
            }
            
        }
        //print_r($addy_by_client); die();
        return array('entity'=>$addy_by_client);

    }

    /**
     * Creates a new client address from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ClientAddressType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "SuRestaurantBundle:ClientAddress:new.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * //@Security("has_role('ROLE_ANONYMOUS','ROLE_USER')")
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postClientaddressAction(Request $request)
    {
        try {
            $newPage = $this->container->get('su_restaurant.clientaddress.handler')->post(
                $request->request->all()
            );
        $statusCode = Response::HTTP_CREATED;
        $routeOptions = array(
            'id' => $newPage->getId(),
            '_format' => $request->get('_format'),
            'status' => $statusCode
        );
        //return $statusCode;
        return $routeOptions;
        //return $this->routeRedirectView('clientaddress', $routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }

    /**
     * Update existing Category from the submitted data or create a new ClientAddress at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ClientAddressType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:ClientAddress:edit.html.twig",
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
    public function putClientaddressAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('su_restaurant.clientaddress.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('su_restaurant.clientaddress.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('su_restaurant.clientaddress.handler')->put(
                    $page,
                    $request->request->all()
                );
            }
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format'),
                'status' => $statusCode
            );
            //return $this->routeRedirectView('get_clientAddress', $routeOptions, $statusCode);
            //return $statusCode;
            return $routeOptions;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update existing Client Address from the submitted data or create a new ClientAddress at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Su\RestaurantBundle\Form\ClientAddressType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "SuRestaurantBundle:ClientAddress:editPage.html.twig",
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
    public function patchClientaddressAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('su_restaurant.clientaddress.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );
            return $this->routeRedirectView('clientaddress', $routeOptions, Response::HTTP_NO_CONTENT);
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
        if (!($page = $this->container->get('su_restaurant.clientaddress.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }
        return $page;
    }


}