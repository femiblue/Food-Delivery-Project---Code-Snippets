<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 25/09/2016
 * Time: 04:49 PM
 */

namespace Su\UserBundle\Entity;


use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("oauth2_clients")
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebookId;

    public function __construct()
    {
        parent::__construct();
    }
}