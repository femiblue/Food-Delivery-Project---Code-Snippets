<?php

/**
 * Created by Femi Bello.
 * User: femiblue
 * Date: 27/11/2016
 * Time: 09:00 AM
 */
 
 
namespace Su\UserBundle\Controller;

use Su\UserBundle\Entity\User;
use Su\UserBundle\Form\FacebookRegistrationType;
use League\OAuth2\Client\Provider\FacebookUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookConnectController extends Controller
{
    /**
     * @Route("/connect/facebook", name="connect_facebook")
     */
    public function connectFacebookAction(Request $request)
    {   
        #echo $request;
        #echo "<br/>";
        #echo "I entered Facebook Action";
        #echo "<br/>"; 
        // redirect to Facebook
        $facebookOAuthProvider = $this->get('app.facebook_provider');

        $url = $facebookOAuthProvider->getAuthorizationUrl([
            // these are actually the default scopes
            'scopes' => ['public_profile', 'email'],
        ]);
        
        #echo "Retuurned URL...".$url;
        #echo "<br/>";
        //die("I stopped");
        return $this->redirect($url);
    }

    /**
     * @Route("/connect/facebook-check", name="connect_facebook_check")
     */
    public function connectFacebookCheckAction()
    {
        // will not be reached!
        //echo "Hello!. Am back!";
        //die("Moje tan");
    }

    /**
     * @Route("/connect/facebook/registration", name="connect_facebook_registration")
     */
    public function finishRegistrationAction(Request $request)
    { 
        // stored in the session in FacebookAuthenticator
        /** @var FacebookUser $facebookUser */
        $facebookUser = $request->getSession()->get('facebook_user');
       // die($facebookUser);
        if (!$facebookUser) {
            //throw $this->createNotFoundException('How did you get here without user information!?');
             $url = $this->container->get('router')->generate('fos_user_security_login');
             return new RedirectResponse($url);
        }
        $user = new User();
        $user->setFacebookId($facebookUser->getId());
        $user->setEmail($facebookUser->getEmail());
        //Get other facebook details
        $user->setUsername($facebookUser->getEmail()); //Set use Email to ensure its distinct
        $user->setEmail($facebookUser->getEmail());
        $plainPassword   = $facebookUser->getName(); //Temporary - use name as password
        $encodedPassword = $this->get('security.password_encoder')
                ->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
        $user->setEnabled(1); //Enable user manually since facebook as already authenticated him/her
        $user->setFullName($facebookUser->getName());
        //print_r($user);die();
        
        if($user){

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // remove the session information
            $request->getSession()->remove('facebook_user');

            // log the user in manually
            $guardHandler = $this->container->get('security.authentication.guard_handler');
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->container->get('app.facebook_authenticator'),
                'main' // the firewall key
            );
        }
        
       
       /* Dont create registration form. Skip and regsiter genuine facebook automatically and log them in system   */
       // $form = $this->createForm(new FacebookRegistrationType(), $user);
       /*
        $form->handleRequest($request);
        if ($form->isValid()) {
       
            // encode the password manually
            $plainPassword = $form['plainPassword']->getData();
            $encodedPassword = $this->get('security.password_encoder')
                ->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // remove the session information
            $request->getSession()->remove('facebook_user');

            // log the user in manually
            $guardHandler = $this->container->get('security.authentication.guard_handler');
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->container->get('app.facebook_authenticator'),
                'main' // the firewall key
            );
        }
        
        */
        //die("Hello");
        /*
        return $this->render('SuUserBundle:Facebook:registration.html.twig', array(
            'form' => $form->createView()
        ));
        */
    }
}
