<?php

namespace Majora\OTAStore\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';
    const TOKEN_MY_ACCOUNT = 'my-account';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, array(
                'label' => 'user.edit.email.label',
            ))
            ->add('firstname', Type\TextType::class, array(
                'label' => 'user.edit.firstname.label',
            ))
            ->add('lastname', Type\TextType::class, array(
                'label' => 'user.edit.lastname.label',
            ))
            ->add('password', Type\RepeatedType::class, array(
                'type' => Type\PasswordType::class,
                'first_options' => array('label' => 'user.edit.password.label.first'),
                'second_options' => array('label' => 'user.edit.password.label.second'),
                'required' => $options['csrf_token_id'] === self::TOKEN_CREATION,
                'mapped' => false,
            ))
        ;

        // Roles can't be set for "my-account" csrf_token_id
        if ($options['csrf_token_id'] !== self::TOKEN_MY_ACCOUNT) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $userRole = $event->getData()->getRole();
                $form = $event->getForm();

                $form->add('roles', Type\ChoiceType::class, array(
                    'label' => 'user.edit.role.label',
                    'choices' => array(
                        'user.roles.ROLE_USER' => 'ROLE_USER',
                        'user.roles.ROLE_ADMIN' => 'ROLE_ADMIN',
                        'user.roles.ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                    ),
                    'choices_as_values' => true,
                    'mapped' => false,
                    'data' => $userRole,
                ));
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Majora\OTAStore\UserBundle\Entity\User',
        ));
    }

    public function getBlockPrefix()
    {
        return 'majoraotastore_user';
    }
}
