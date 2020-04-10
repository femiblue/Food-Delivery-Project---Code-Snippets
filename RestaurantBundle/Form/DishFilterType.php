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
            ->add('dishId', 'filter_number_range')
            ->add('name', 'filter_text')
            ->add('description', 'filter_text')
            ->add('price', 'filter_number_range')
            ->add('image', 'filter_text')
            ->add('category', 'filter_number_range')
			->add('shop', 'filter_number_range')
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
        return 'su_restaurantbundle_dishfiltertype';
    }
}
