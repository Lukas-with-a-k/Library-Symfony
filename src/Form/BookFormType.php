<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class BookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
            ])
            ->add('publicationDate', DateType::class, [
                'label' => 'Date de Publication',
                'widget' => 'single_text',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Excellent' => 'excellent',
                    'Bon' => 'good',
                    'Moyen' => 'fair',
                    'Mauvais' => 'poor',
                ],
            ])
            ->add('genres', TextType::class, [
                'label' => 'Genres (séparés par des virgules)',
                'mapped' => false,
                'data' => isset($options['data']) ? implode(', ', $options['data']->getGenres() ?? []) : '',
            ])
            
            ->add('coverFile', FileType::class, [
                'label' => 'Couverture (fichier)',
                'mapped' => false, 
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
