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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends ContainerAware
{
    /**
     * Show the user
     */
    public function showAction()
    {
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $cartObj          = $this->container->get('cart');
        $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $this->container->get('templating')->renderResponse('SuUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine')
        , array(
        'user'               => $user,
        'entity_cat'         => $results_cat,
        'num_cart_items'     => $num_cart_items,
        'page_title'         => "My Account",
       ));
    }

    /**
     * Edit the user
     */
    public function editAction()
    {
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $cartObj          = $this->container->get('cart');
        $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->container->get('fos_user.profile.form');
        $formHandler = $this->container->get('fos_user.profile.form.handler');

        $process = $formHandler->process($user);
        if ($process) {
            $this->setFlash('fos_user_success', 'profile.flash.updated');

            return new RedirectResponse($this->getRedirectionUrl($user));
        }

        return $this->container->get('templating')->renderResponse(
            'SuUserBundle:Profile:edit.html.'.$this->container->getParameter('fos_user.template.engine'),
            array(
                'form' => $form->createView(),
                'entity_cat'         => $results_cat,
                'num_cart_items'     => $num_cart_items,
                'page_title'         => "Edit My Account",
            ));
    }

    /**
     * Generate the redirection url when editing is completed.
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
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }
}
