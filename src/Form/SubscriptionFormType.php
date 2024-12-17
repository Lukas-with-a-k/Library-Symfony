<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class SubscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subscriptionType', ChoiceType::class, [
                'choices' => [
                    'Monthly (23,99 €)' => 'monthly',
                    'Yearly (259,99 €)' => 'yearly',
                ],
                'multiple' => false,
                'label' => 'Choose your subscription',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Confirm Password',
                'required' => true,
            ]);
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
