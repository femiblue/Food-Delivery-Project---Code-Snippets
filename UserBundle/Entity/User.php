<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 25/09/2016
 * Time: 03:31 PM
 */

namespace Su\UserBundle\Entity;


use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("client")
 */
class User extends BaseUser
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
     * @ORM\Column(name="full_name", type="string", length=50, nullable=false)
     */
    protected $fullName;
    
     /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebookId;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updateDate;


    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->setCreationDate(new \DateTime('now'));
    }
    
     /**
     * Set email
     *
     * @param string $email
     *
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return Client
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }
    
        /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return Client
     */
    public function setfacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }
    
       /**
     * Get facebookId
     *
     * @return string
     */
    public function getfacebookId()
    {
        return $this->facebookId;
    }
    

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Client
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     *
     * @return Client
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdateDate(new \DateTime('now'));

        if ($this->setCreationDate() == null) {
            $this->setCreationDate(new \DateTime('now'));
        }
    }

}