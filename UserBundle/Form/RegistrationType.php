<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 01/10/2016
 * Time: 10:02 AM
 */

namespace Su\UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fullName');
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getBlockPrefix()
    {
        return 'su_user_registration';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }


}