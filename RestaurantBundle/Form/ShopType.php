<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ShopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shopName')
            ->add('shopDescription')
            ->add('shopLogoFile', 'file', array(
                'label' => 'Upload Shop Logo'
           ))
		   ->add('shopLocation')
		   ->add('shopZipcode')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Su\RestaurantBundle\Entity\Shop',
            'csrf_protection'   => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'su_restaurantbundle_shop';
    }
}
