<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('search', TextType::class, [
                'label' => false,
                'attr'  => [
                    'class'       => 'search_input',
                    'placeholder' => 'Recherche...',
                    'minlenght'   => 3,
                ],
            ])
            ->add('city_id', HiddenType::class, [
                'attr' => [
                    'class' => 'city_id',
                ],
            ])
            ->add('city_lat', HiddenType::class, [
                'attr' => [
                    'class' => 'city_lat',
                ],
            ])
            ->add('city_lon', HiddenType::class, [
                'attr' => [
                    'class' => 'city_lon',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
