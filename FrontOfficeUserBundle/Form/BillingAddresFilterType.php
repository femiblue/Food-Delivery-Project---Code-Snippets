<?php

namespace Su\FrontOfficeUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class BillingAddresFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'filter_number_range')
            ->add('clientName', 'filter_text')
            ->add('address1', 'filter_text')
            ->add('address2', 'filter_text')
            ->add('city', 'filter_text')
            ->add('zipcode', 'filter_text')
            ->add('nickname', 'filter_text')
            ->add('email', 'filter_text')
            ->add('phone', 'filter_text')
            ->add('clientId', 'filter_number_range')
            //->add('creationDate', 'filter_date_range')
            //->add('updateDate', 'filter_date_range')
        ;

        $listener = function(FormEvent $event)
        {
            // Is data empty?
            foreach ($event->getData() as $data) {
                if(is_array($data)) {
                    foreach ($data as $subData) {
                        if(null !== $subData) return;
                    }
                }
                else {
                    if(null !== $data) return;
                }
            }

            $event->getForm()->addError(new FormError('Filter empty'));
        };
        $builder->addEventListener(FormEvents::POST_BIND, $listener);
    }

    public function getName()
    {
        return 'su_frontofficeuserbundle_billingaddressfiltertype';
    }
}
