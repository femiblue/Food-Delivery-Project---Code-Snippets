<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, array(
                'label' => 'Dish Category',
                'class' => 'SuRestaurantBundle:Category',
                'choice_label' => 'name',
                'choice_value' => 'category_id',
            ))
			->add('shop', EntityType::class, array(
                'label' => 'Shop',
                'class' => 'SuRestaurantBundle:Shop',
                'choice_label' => 'shop_name',
                'choice_value' => 'shop_id',
            ))
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('imageFile', 'file', array(
                'label' => 'Upload Dish Image'
           ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\Dish',
            'csrf_protection'   => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'su_restaurantbundle_dish';
    }
}
