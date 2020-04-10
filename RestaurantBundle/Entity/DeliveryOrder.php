<?php

namespace Su\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Su\RestaurantBundle\Entity\ClientOrder;
//use Su\RestaurantBundle\Entity\ClientAddress;
//use Su\UserBundle\Entity\User;

/**
 * DeliveryOrder
 *
 * @ORM\Table(name="delivery_order", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="clientId", columns={"client_id"}), @ORM\Index(name="address_id", columns={"address_id"}), @ORM\Index(name="client_order_id", columns={"client_order_id"})})
 * @ORM\Entity
 */
class DeliveryOrder
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
     * @var integer
     *
     * @ORM\Column(name="client_order_id", type="integer", nullable=true)
     */
    private $clientOrderId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="address_id", type="integer", nullable=true)
     */
    private $addressId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer", nullable=true)
     */
    private $clientId;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;
    
   /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=false)
     */
    private $updateDate;
    



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
     * Set clientOrderId
     *
     * @param integer $clientOrderId
     *
     * @return DeliveryOrder
     */
    public function setClientOrderId($clientOrderId)
    {
        $this->clientOrderId = $clientOrderId;

        return $this;
    }

    /**
     * Get clientOrderId
     *
     * @return integer
     */
    public function getClientOrderId()
    {
        return $this->clientOrderId;
    }
    
    /**
     * Set addressId
     *
     * @param integer $addressId
     *
     * @return DeliveryOrder
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;

        return $this;
    }

    /**
     * Get addressId
     *
     * @return integer
     */
    public function getAddressId()
    {
        return $this->addressId;
    }

    
    /**
     * Set clientId
     *
     * @param integer $clientId
     *
     * @return DeliveryOrder
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
     * Set status
     *
     * @param integer $status
     *
     * @return DeliveryOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     *
     * @return DeliveryOrder
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
    }
/*
    public function __toString() {
        return $this->id;
    }
*/
   
}
