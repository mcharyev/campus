<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserResetPasswordFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //echo $entity->getGroupName();
        $roles = implode(",", $entity->getRoles());
        $builder
                ->add('username', TextType::class, [
                    'mapped' => true,
                    'attr' => [
                        'style' => 'width:300px;'
                    ],
                    'disabled' => true,
                    'label' => 'Username']
                )
                ->add('password', HiddenType::class, ['label' => 'Password'])
                ->add('firstname', TextType::class, [
                    'mapped' => true,
                    'attr' => [
                        'style' => 'width:300px;'
                    ],
                    'disabled' => true,
                    'label' => 'Firstname'])
                ->add('lastname', TextType::class, [
                    'mapped' => true,
                    'attr' => [
                        'style' => 'width:300px;'
                    ],
                    'disabled' => true,
                    'label' => 'Lastname'])
                ->add('email', TextType::class, [
                    'mapped' => true,
                    'attr' => [
                        'style' => 'width:300px;'
                    ],
                    'disabled' => true,
                    'label' => 'Email'])
                ->add('source_path', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Return path',
                    'data' => $options['source_path'],
                    'required' => false
                ])
        ;

        $builder
                ->add('newPassword1', TextType::class, [
                    'mapped' => false,
                    'label' => 'New password',
                    'required' => false,
                    'help' => 'Do not set if you don\'t want to change'
                ])
                ->add('newPassword2', TextType::class, [
                    'mapped' => false,
                    'label' => 'New password repeat',
                    'required' => false,
                    'help' => 'Repeat the password exactly'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
            'source_path' => '',
            'is_admin' => false
        ]);
    }

}
