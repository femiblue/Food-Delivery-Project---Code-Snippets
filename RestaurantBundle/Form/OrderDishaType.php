<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrderDishaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientAddress')
            ->add('dish')
            ->add('quantity')
            ->add('note')
            ->add('price')
            //->add('creationDate')
            //->add('updateDate')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\OrderDish',
            'csrf_protection'   => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'su_restaurantbundle_orderdish';
    }
}
