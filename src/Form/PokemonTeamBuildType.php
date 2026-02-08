<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PokemonTeamBuildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name1', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '1er pokémon']])
            ->add('name2', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '2ème pokémon']])
            ->add('name3', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '3ème pokémon']])
            ->add('name4', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '4ème pokémon']])
            ->add('name5', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '5ème pokémon']])
            ->add('name6', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => '6ème pokémon']])
            // ->add('name1', ChoiceType::class, [
            //     'choices' => ['bulbizarre' => 1, 'salameche' => 4, 'carapuce' => 7],
            //     'required' =>  false
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
