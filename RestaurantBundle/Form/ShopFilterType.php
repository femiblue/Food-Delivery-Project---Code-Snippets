<?php

namespace Su\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class DishFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shopId', 'filter_number_range')
            ->add('shopName', 'filter_text')
            ->add('shopDescription', 'filter_text')
            ->add('shopLogo', 'filter_text')
			->add('shopLocation', 'filter_text')
            ->add('shopZipcode', 'filter_text')
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
        return 'su_restaurantbundle_shopfiltertype';
    }
}
