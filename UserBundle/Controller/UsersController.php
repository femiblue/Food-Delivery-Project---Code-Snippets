<?php
/**
 * Created by PhpStorm.
 * User: blue
 * Date: 01/10/2016
 * Time: 07:13 AM
 */

namespace Su\UserBundle\Controller;


use Su\UserBundle\Entity\User;
use Su\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
class UsersController extends FOSRestController implements ClassResourceInterface
{

    public function cgetAction()
    {

        //security.yml is configured to allow anonymous access to controllers
        //checking for authorization in each controller allows more flexibility
        //to change this remove anonymous: true in security.yml on firewall
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("SuUserBundle:User");
        $users = $repository->findAll();
        $view = $this->view($users, 200)
            ->setTemplate("default/users.html.twig")
            ->setTemplateVar('users')
        ;
        return $this->handleView($view);
    }
    public function postAction(Request $request){
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $user->setPlainPassword($user->getPassword());
            $userManager->updateUser($user);
            $view = $this->view($user, 200);
            return $this->handleView($view);
        }
        $view = $this->view($form->getErrors(), 409);
        return $this->handleView($view);
    }
}