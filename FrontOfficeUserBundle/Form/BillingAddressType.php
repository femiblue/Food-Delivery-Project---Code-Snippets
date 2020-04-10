<?php

namespace Su\FrontOfficeUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
class BillingAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {          
        $builder
            /*->add('clientId', EntityType::class, array(
                'label' => 'User Account',
                'class' => 'SuUserBundle:User',
                'placeholder' => 'Choose User Account',
                'required' => false,
                'empty_data'  => null,
                'choice_label' => 'fullName',
                'choice_value' => 'id',
            ))*/
            //->add('clientId', 'hidden', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Client')))
            ->add('clientName', 'text', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Full Name')))
            ->add('address1', 'textarea', array('label' => ' ','attr' => array('cols' => '5', 'rows' => '5','placeholder' => 'Address','class' => 'address_form')))
            ->add('address2', 'textarea', array('label' => ' ','required' => false,'attr' => array('cols' => '5', 'rows' => '5','placeholder' => 'Other Address','class' => 'address_form')))
            ->add('city', 'text', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'City')))
            ->add('zipcode', 'text', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Zipcode')))
            ->add('nickname', 'text', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Nick Name')))
            ->add('email', 'email', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Email')))
            ->add('phone', 'text', array('label' => ' ', 'attr' => array('class' => 'address_form','placeholder' => 'Phone')))
            
            
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
        //return 'su_frontofficeuserbundle_billingaddress';
        return "";
    }
}
