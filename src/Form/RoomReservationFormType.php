<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\RoomReservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class RoomReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoomReservation::class,
        ]);
    }
}

