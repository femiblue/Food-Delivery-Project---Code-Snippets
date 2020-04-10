<?php

namespace Su\UserBundle\OAuth;

use Exception;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FinishFacebookRegistrationException extends AuthenticationException
{
    private $facebookUser;

    public function __construct(ResourceOwnerInterface $facebookUser, $message = "", $code = 0, Exception $previous = null)
    {
        $this->facebookUser = $facebookUser;

        parent::__construct($message, $code, $previous);
    }

    public function getFacebookUser()
    {
        return $this->facebookUser;
    }
}
