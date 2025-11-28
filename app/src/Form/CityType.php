<?php

namespace App\Form;

use App\Service\CityLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CityType extends AbstractType
{
    private CityLoader $cityLoader;

    public function __construct(CityLoader $cityLoader)
    {
        $this->cityLoader = $cityLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('city', ChoiceType::class, [
            'choices' => $this->cityLoader->getCities(),
            'placeholder' => 'Choisissez une ville',
             'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}