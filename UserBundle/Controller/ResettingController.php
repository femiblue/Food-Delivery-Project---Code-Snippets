<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Su\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;

use Symfony\Component\Security\Core\SecurityContext;
use Su\RestaurantBundle\Entity\category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends ContainerAware
{
    const SESSION_EMAIL = 'fos_user_send_resetting_email/email';

    /**
     * Request reset user password: show form
     */
    public function requestAction()
    {   
        $landingObj = $this->container->get('landing');
        $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
        
        $cartObj        = $this->container->get('cart');
        $num_cart_items = $cartObj->getNumberOfCartItemsAction();
        $results_cat    = $landingObj->getTopMenuAction();//print_r($results_cat);
        
        return $this->container->get('templating')->renderResponse('SuUserBundle:Resetting:request.html.'.$this->getEngine(),
            array(
             'entity_cat'      => $results_cat,
             'page_title'    => "Reset Password",
             'num_cart_items'  => $num_cart_items,
            ));
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction()
    { 
        $landingObj = $this->container->get('landing');
        $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
        
        $cartObj        = $this->container->get('cart');
        $num_cart_items = $cartObj->getNumberOfCartItemsAction();
        $results_cat    = $landingObj->getTopMenuAction();//print_r($results_cat);
        
        $username = $this->container->get('request')->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('SuUserBundle:Resetting:request.html.'.$this->getEngine(), 
            array('invalid_username' => $username,
                  'entity_cat'      => $results_cat,
                  'page_title'    => "Reset Password - User does not exist",
                  'num_cart_items'  => $num_cart_items,
             ));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('SuUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine(),
            array('entity_cat'      => $results_cat,
                  'page_title'    => "Reset Password - Password Already Requested",
                  'num_cart_items'  => $num_cart_items,
             ));
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'),
            array(
             'entity_cat'      => $results_cat,
             'page_title'    => "Reset Password - Send Mail",
             'num_cart_items'  => $num_cart_items,
            ));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $landingObj = $this->container->get('landing');
        $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
        
        $cartObj        = $this->container->get('cart');
        $num_cart_items = $cartObj->getNumberOfCartItemsAction();
        $results_cat    = $landingObj->getTopMenuAction();//print_r($results_cat);
        
        $session = $this->container->get('session');
        $email = $session->get(static::SESSION_EMAIL);
        $session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        return $this->container->get('templating')->renderResponse('SuUserBundle:Resetting:checkEmail.html.'.$this->getEngine(), array(
            'email' => $email,
            array(
             'entity_cat'      => $results_cat,
             'page_title'    => "Reset Password",
             'num_cart_items'  => $num_cart_items,
            )
        ));
    }

    /**
     * Reset user password
     */
    public function resetAction($token)
    {
        $landingObj = $this->container->get('landing');
        $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
        
        $cartObj        = $this->container->get('cart');
        $num_cart_items = $cartObj->getNumberOfCartItemsAction();
        $results_cat    = $landingObj->getTopMenuAction();//print_r($results_cat);
        
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        $form = $this->container->get('fos_user.resetting.form');
        $formHandler = $this->container->get('fos_user.resetting.form.handler');
        $process = $formHandler->process($user);

        if ($process) {
            $this->setFlash('fos_user_success', 'resetting.flash.success');
            $response = new RedirectResponse($this->getRedirectionUrl($user));
            $this->authenticateUser($user, $response);

            return $response;
        }

        return $this->container->get('templating')->renderResponse('SuUserBundle:Resetting:reset.html.'.$this->getEngine(), array(
            'token' => $token,
            'form' => $form->createView(),
            array(
             'entity_cat'      => $results_cat,
             'page_title'    => "Reset Password",
             'num_cart_items'  => $num_cart_items,
            )
        ));
    }

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Response $response)
    {
        try {
            $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user,
                $response);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    /**
     * Generate the redirection url when the resetting is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_user_profile_show');
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
}
