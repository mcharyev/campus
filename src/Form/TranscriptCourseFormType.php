<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use App\Entity\TranscriptCourse;
use App\Enum\GradedCourseCreditTypeEnum;
use App\Enum\GradedCourseTypeEnum;
use App\Entity\ProgramCourseType;

class TranscriptCourseFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();

        $builder
                ->add('letterCode', TextType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'label' => 'Letter Code'
                ])
                ->add('nameEnglish', TextType::class, [
                    'label' => 'Name English'
                ])
                ->add('nameTurkmen', TextType::class, [
                    'label' => 'Name Turkmen'
                ])
                ->add('creditType', ChoiceType::class, [
                    'choices' => GradedCourseCreditTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Course credit type',
                    'help' => 'Enter course credit type'
                ])
                ->add('courseType', ChoiceType::class, [
                    'choices' => GradedCourseTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Course type',
                    'help' => 'Enter course type'
                ])
                ->add('studentId', TextType::class, [
                    'attr' => ['style' => 'width:200px;'],
                    'label' => 'Student Id',
                ])
                ->add('studentName', TextType::class, [
                    'label' => 'Student Name',
                ])
                ->add('year', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Year',
                    'help' => 'Academic year in which the course was taught'
                ])
                ->add('semester', ChoiceType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                        '6' => 6,
                        '7' => 7,
                        '8' => 8,
                        '9' => 9,
                        '10' => 10
                    ],
                    'empty_data' => 1,
                    'expanded' => false,
                    'multiple' => false,
                    'help' => 'Semester in the study program'
                ])
                ->add('status', ChoiceType::class, [
                    'attr' => ['style' => 'width:300px;'],
                    'choices' => [
                        'Open' => 1,
                        'Closed' => 0,
                    ]
                ])
                ->add('midterm', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Midterm grade',
                ])
                ->add('final', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Final grade',
                ])
                ->add('makeup', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Make-up grade',
                ])
                ->add('siwsi', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'SIWSI grade',
                ])
                ->add('courseGrade', TextType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Course final grade',
                ])
                ->add('teacher', TextType::class, [
                    'label' => 'Teacher',
                    'help' => 'Name of the teacher who graded the course'
                ])
                ->add('lastUpdater', TextType::class, [
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $entity->getLastupdater(),
                    'label' => 'Last updating user',
                    'help' => 'Username which updated the record'
                ])
                ->add('dateUpdated', DateType::Class, [
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $entity->getDateUpdated(),
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Last updated'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => TranscriptCourse::class,
        ]);
    }

}
