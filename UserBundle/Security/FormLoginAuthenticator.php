<?php

/**
 * Created by Femi bello.
 * User: femiblue
 * Date: 27/11/2016
 * Time: 09:00 AM
 */

namespace Su\UserBundle\Security;

use KnpU\Guard\Authenticator\AbstractFormLoginAuthenticator;
use KnpU\Guard\Exception\CustomAuthenticationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FormLoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login_check') {
            return;
        }

        $username = $request->request->get('_username');
        $request->getSession()->set(Security::LAST_USERNAME, $username);
        $password = $request->request->get('_password');

        return array(
            'username' => $username,
            'password' => $password
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        // a silly example of failing with a custom message
        if ($username == 'rails_troll') {
            throw CustomAuthenticationException::createWithSafeMessage(
                'Get outta here rails_troll - we don\'t like you!'
            );
        }

        $userRepo = $this->container
            ->get('doctrine')
            ->getManager()
            ->getRepository('SuUserBundle:User');

        return $userRepo->findByUsernameOrEmail($username);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $plainPassword = $credentials['password'];
        $encoder = $this->container->get('security.password_encoder');
        if (!$encoder->isPasswordValid($user, $plainPassword)) {
            // throw any AuthenticationException
            throw new BadCredentialsException();
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // AJAX! Maybe return some JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                // you could translate the message
                array('message' => $exception->getMessageKey()),
                403
            );
        }

        // for non-AJAX requests, return the normal redirect
        return parent::onAuthenticationFailure($request, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // AJAX! Return some JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                // maybe send back the user's id
                array('userId' => $token->getUser()->getId())
            );
        }

        // for non-AJAX requests, return the normal redirect
        return parent::onAuthenticationSuccess($request, $token, $providerKey);
    }

    protected function getLoginUrl()
    {
        return $this->container->get('router')
            ->generate('fos_user_security_login');
    }

    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->container->get('router')
            ->generate('front_office_user_homepage');
    }
}
