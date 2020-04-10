<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ClientOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        $builder
            //->add('orderDish', new OrderDishType())
            ->add('orderDishId', EntityType::class, array(
                'label' => 'Order Dish',
                'class' => 'SuRestaurantBundle:OrderDish',
                'choice_label' => 'note',
                'choice_value' => 'id',
            ))
            ->add('deliveryFee')
            ->add('tax')
            ->add('total')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\ClientOrder',
            'csrf_protection'   => false,
            'allow_extra_fields' => true,
        ));
    }


    public function getName()
    {
        return 'su_restaurantbundle_clientorder';
    }
}
