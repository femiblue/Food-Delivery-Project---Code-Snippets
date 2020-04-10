<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClientAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientId', EntityType::class, array(
                'label' => 'User Account',
                'class' => 'SuUserBundle:User',
                'placeholder' => 'Choose User Account',
                'required' => false,
                'empty_data'  => null,
                'choice_label' => 'fullName',
                'choice_value' => 'id',
            ))
            ->add('clientName')
            ->add('address1', 'textarea', array('attr' => array('cols' => '5', 'rows' => '5')))
            ->add('address2', 'textarea', array('attr' => array('cols' => '5', 'rows' => '5','required' => false)))
            ->add('city')
            ->add('zipcode')
            ->add('nickname')
            ->add('email')
            ->add('phone')
            
            //->add('creationDate')
            //->add('updateDate')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\ClientAddress',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return 'su_restaurantbundle_clientaddress';
    }
}
