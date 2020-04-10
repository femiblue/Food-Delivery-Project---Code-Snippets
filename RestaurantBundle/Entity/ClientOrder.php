<?php

namespace Su\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Su\RestaurantBundle\Entity\OrderDish;

/**
 * ClientOrder
 *
 * @ORM\Table(name="client_order", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="order_dish_id", columns={"order_dish_id"})})
 * @ORM\Entity
 */
class ClientOrder
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
     * @ORM\Column(name="order_dish_id", type="integer", nullable=true)
     */
    private $orderDishId;

    /**
     * @var float
     *
     * @ORM\Column(name="delivery_fee", type="float", precision=3, scale=2, nullable=false)
     */
    private $deliveryFee;

    /**
     * @var float
     *
     * @ORM\Column(name="tax", type="float", precision=3, scale=2, nullable=false)
     */
    private $tax;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", precision=4, scale=2, nullable=false)
     */
    private $total;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private $creationDate;

    public function __construct(){
        //
        $this->setCreationDate(new \DateTime('now'));

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
     * Set orderDishId
     *
     * @param integer $orderDishId
     *
     * @return ClientOrder
     */
    public function setOrderDishId($orderDishId)
    {
        $this->orderDishId = $orderDishId;

        return $this;
    }

    /**
     * Get orderDishId
     *
     * @return integer
     */ 
    public function getOrderDishId()
    {
        return $this->orderDishId;
    }

    /**
     * Set deliveryFee
     *
     * @param float $deliveryFee
     *
     * @return ClientOrder
     */
    public function setDeliveryFee($deliveryFee)
    {
        $this->deliveryFee = $deliveryFee;

        return $this;
    }

    /**
     * Get deliveryFee
     *
     * @return float
     */
    public function getDeliveryFee()
    {
        return $this->deliveryFee;
    }

    /**
     * Set tax
     *
     * @param float $tax
     *
     * @return ClientOrder
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set total
     *
     * @param float $total
     *
     * @return ClientOrder
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return ClientOrder
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
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setCreationDate(new \DateTime('now'));

    }

    public function __toString() {
        return $this->id;
    }

}
