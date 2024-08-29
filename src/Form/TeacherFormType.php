<?php

namespace App\Form;

use App\Entity\Teacher;
use App\Entity\Department;
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

class TeacherFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('firstname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname'])
                ->add('lastname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname'])
                ->add('patronym', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Patronym', 'required' => false])
                ->add('scheduleName', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Schedule name', 'required' => false])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Department'])
                ->add('level', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'junior-instructor' => '1',
                        'instructor' => '2',
                        'senior instructor' => '3',
                        'associate professor' => '4',
                        'professor' => '5'
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Teaching Level'
                ])
                ->add('degree', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'bachelor' => '1',
                        'specialist' => '2',
                        'masters' => '3',
                        'PhD' => '4',
                        'Cand.Sci.' => '5'
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Academic Degree'
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => '1',
                        'Disabled' => '0',
                    ],
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Status'
                ])
                ->add('source_path', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Return path',
                    'data' => $options['source_path'],
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Teacher::class,
            'source_path' => ''
        ]);
    }

}
