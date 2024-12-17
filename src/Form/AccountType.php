<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
