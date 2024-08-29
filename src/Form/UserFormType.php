<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserTypeEnum;
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

class UserFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();

        $data = $entity->getData();
        //$modelData = json_decode($data);
        if ($data == null) {
            $data = [
                'security_question1' => '',
                'security_answer1' => '',
                'security_question2' => '',
                'security_answer2' => '',
            ];

            $entity->setData($data);
        }

        $roles = implode(",", $entity->getRoles());
        if ($options['is_admin']) {
            $builder->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                    ->add('username', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Username'])
                    ->add('type', ChoiceType::class, [
                        'choices' => UserTypeEnum::getChoiceTypeArray(),
                        'empty_data' => '0',
                        'expanded' => true,
                        'multiple' => false,
                        'label' => 'User Type'
                    ])
                    ->add('roles', TextAreaType::class, [
                        'data' => $roles,
                        'label' => 'Roles',
                        'mapped' => false
            ]);
        }
        $builder
                ->add('password', HiddenType::class, ['label' => 'Password'])
                ->add('firstname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname'])
                ->add('lastname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname'])
                ->add('email', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Email', 'required' => false])
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
                ->add('security_question1', TextType::class, [
                    'mapped' => false,
                    'label' => 'Security question 1',
                    'data' => $entity->getDataField('security_question1'),
                    'required' => false,
                    'help'=>'Security question which will be asked if your forget your password'
                ])
                ->add('security_answer1', TextType::class, [
                    'mapped' => false,
                    'label' => 'Security answer 1',
                    'data' => $entity->getDataField('security_answer1'),
                    'required' => false,
                    'help'=>'Security answer if your forget your password'
                ])
                ->add('security_question2', TextType::class, [
                    'mapped' => false,
                    'label' => 'Security question 2',
                    'data' => $entity->getDataField('security_question2'),
                    'required' => false,
                    'help'=>'Security question which will be asked if your forget your password'
                ])
                ->add('security_answer2', TextType::class, [
                    'mapped' => false,
                    'label' => 'Security answer 2',
                    'data' => $entity->getDataField('security_answer2'),
                    'required' => false,
                    'help'=>'Security answer if your forget your password'
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
