<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 26/09/2016
 * Time: 06:17 AM
 */

namespace Su\RestaurantBundle\Controller\Api\Rest;




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

class AuthClientController extends FOSRestController
{

    /**
     * Generate client_id and Client_secret.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postAuthclientAction(Request $request)
    {
        try {
            $grant_arr    = array('client_credentials','password','refresh_token','authorization_code');
            $grant_type   = $request->get('grant_type');

            $d_grant_type = ( ($grant_type != '') && (in_array($grant_type,$grant_arr))) ? $grant_type : 'password';
            $clientManager = $this->get('fos_oauth_server.client_manager.default');

            $client = $clientManager->createClient();

            $client->setAllowedGrantTypes(array($d_grant_type));
            $clientManager->updateClient($client);

            $output = array(
                        'client_id'=>$client->getPublicId(),
                        'client_secret' => $client->getSecret()
                        );

            return $output;

        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

    }




}