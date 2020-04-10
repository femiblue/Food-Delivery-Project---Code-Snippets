<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DeliveryOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, array(
            'choices'  => array(
                'Placed'    => 0,
                'Confirmed' => 1,
                'Shipped'   => 2,
                'Delivered' => 3,
                'Cancelled' => 3,
              ),
             ))
            ->add('clientOrderId')
            ->add('addressId')
            ->add('clientId')
        ;
    }

   public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\DeliveryOrder',
            'csrf_protection'   => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'su_restaurantbundle_deliveryorder';
    }


}
