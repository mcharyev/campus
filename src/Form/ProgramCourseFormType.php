<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\ProgramCourse;
use App\Entity\ProgramCourseType;
use App\Enum\GradedCourseCreditTypeEnum;
use App\Entity\Language;
use App\Entity\Department;
use App\Entity\StudyProgram;
use App\Entity\ProgramModule;

class ProgramCourseFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        $data = $entity->getData();
        $note = $entity->getNote();
        //echo $entity->getData();

        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:200px;'], 'label' => 'System ID', 'required'=>false])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:200px;'], 'label' => 'Letter Code', 'required'=>false])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:500px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:500px;'], 'label' => 'Name Turkmen'])
                ->add('module', EntityType::class, ['class' => ProgramModule::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Module'])
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
                    'multiple' => false
                ])
                ->add('creditType', ChoiceType::class, [
                    'choices' => GradedCourseCreditTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Course credit type',
                    'help' => 'Enter course credit type'
                ])
                ->add('type', EntityType::class, ['class' => ProgramCourseType::class,
                    'choice_label' => 'nameEnglish',
                    'expanded' => true,
                    'choice_value' => 'id',
                    'label' => 'Program Course Type'])
                ->add('studyProgram', EntityType::class, ['class' => StudyProgram::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getNameEnglish()." - ".$entity->getLetterCode();
                    },
                    'choice_value' => 'id',
                    'data' => $options['studyProgram'],
                    'label' => 'Study Program'])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Taught by Department'])
                ->add('language', EntityType::class, ['class' => Language::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Course Language'])
                ->add('viewOrder', IntegerType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'View order',
                    'empty_data' => '0'
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => '1',
                        'Disabled' => '0',
                    ],
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Note',
                    'data' => $note,
                    'required' => false
                ])
                ->add('counts', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Course Counts',
                    'data' => $entity->getCountsAsString()
                ])
                ->add('source_path', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Return path',
                    'data' => $options['source_path'],
                    'required' => false
                ])
        //->add('data')

        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ProgramCourse::class,
            'source_path' => '',
            'studyProgram' => null
        ]);

        $resolver->setAllowedTypes('source_path', 'string');
    }

}
