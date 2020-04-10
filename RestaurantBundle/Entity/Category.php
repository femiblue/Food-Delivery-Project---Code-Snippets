<?php

namespace Su\RestaurantBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @JMS\ExclusionPolicy("ALL")
 * @ORM\Entity
 */
class Category
{
    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    private $categoryId;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Expose
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     * @Assert\NotBlank(message="Category Name cannot be empty")
     */
    private $name;



    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


}
