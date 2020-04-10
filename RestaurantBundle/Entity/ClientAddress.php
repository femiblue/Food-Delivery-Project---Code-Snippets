<?php

namespace Su\RestaurantBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Su\RestaurantBundle\Entity\OrderDish;

/**
 * ClientAddress
 *
 * @ORM\Table(name="client_address", uniqueConstraints={@ORM\UniqueConstraint(name="client_name", columns={"client_name,email"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientAddress
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=150, nullable=true)
     */
    private $clientName;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=256, nullable=false)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=256, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=100, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=10, nullable=false)
     */
    private $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=50, nullable=false)
     */
    private $nickname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;
     
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=100, nullable=true)
     */
    private $phone;

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
     
     /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer", nullable=true)
     */
    private $clientId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="mkdefault", type="string", length=1, nullable=false)
     */
    private $mkdefault;
    


    public function __construct(){
        //
        $this->setCreationDate(new \DateTime());
        $this->setUpdateDate(new \DateTime());

    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set clientName
     *
     * @param string $clientName
     *
     * @return ClientAddress
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set address1
     *
     * @param string $address1
     *
     * @return ClientAddress
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return ClientAddress
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return ClientAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     *
     * @return ClientAddress
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set nickname
     *
     * @param string $nickname
     *
     * @return ClientAddress
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }
    
    /**
     * Set email
     *
     * @param string $email
     *
     * @return ClientAddress
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
     * Set phone
     *
     * @param string $phone
     *
     * @return ClientAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return ClientAddress
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
     * @return ClientAddress
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
        $this->setUpdateDate(new \DateTime());

        if ($this->setCreationDate() == null) {
            $this->setCreationDate(new \DateTime());
        }
    }



    /**
     * Set clientId
     *
     * @param integer $clientId
     *
     * @return ClientAddress
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return integer
     */
    public function getClientId()
    {
        return $this->clientId;
    }
    
    /**
     * Set mkdefault
     *
     * @param string $mkdefault
     *
     * @return ClientAddress
     */
    public function setMkdefault($mkdefault)
    {
        $this->mkdefault = $mkdefault;

        return $this;
    }

    /**
     * Get mkdefault
     *
     * @return string
     */
    public function getMkdefault()
    {
        return $this->mkdefault;
    }
}
