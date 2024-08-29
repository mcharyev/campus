<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\StudentAbsence;
use App\Entity\TaughtCourse;
use App\Entity\EnrolledStudent;
use App\Entity\Teacher;
use App\Entity\ClassType;

class StudentAbsenceFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('date', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('student', EntityType::class, ['class' => EnrolledStudent::class,
                    'choice_label' => 'fullname',
                    'choice_value' => 'id',
                    'label' => 'Student'
                ])
                ->add('course', EntityType::class, ['class' => TaughtCourse::class,
                    'choice_label' => 'FullName',
                    'choice_value' => 'id',
                    'label' => 'Course'])
                ->add('author', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher'])
                ->add('classType', EntityType::class, ['class' => ClassType::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Class Type',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('session', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                        '6' => 6,
                        '7' => 7,
                    ],
                    'label' => 'Session',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('status', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        'Unrecovered' => 0,
                        'Recovered' => 1,
                    ],
                    'label' => 'Status',
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('note')

        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => StudentAbsence::class,
        ]);
    }

}
