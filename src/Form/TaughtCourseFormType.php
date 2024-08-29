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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Form\Type\StudentGroupsType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use App\Entity\Department;
use App\Entity\Teacher;

class TaughtCourseFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        $data = $entity->getData();
        if ($data == null) {
            $data = [
                'note' => '',
                'lecture_topics' => '',
                'practice_topics' => '',
                'groups' => $options['studentGroups'],
                'teacher' => '',
                'coursecode' => '',
                'course_name' => $options['courseTitle'],
                'seminar_combined' => 0
            ];

            if ($options['teacher']) {
                $data['teacher'] = $options['teacher']->getShortFullname();
                $entity->setTeacher($options['teacher']);
            }

            if ($options['department']) {
                $entity->setDepartment($options['department']);
            }

            $entity->setData($data);
            $entity->setStartDate($options['startDate']);
            $entity->setEndDate($options['endDate']);
            $entity->setYear($options['year']);
            $entity->setSemester($options['semester']);
            $entity->setCourseTitle($options['courseTitle']);
            $entity->setStudentGroups($options['studentGroups']);
        }

        if ($options['disabledType'] == 1) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        $builder
//                ->add('programCourse', HiddentType::class, ['class' => ProgramCourse::class,
//                    'attr' => ['style' => 'display:none;'],
//                    'choice_label' => function($entity = null) {
//                        return $entity->getNameEnglish() . " - " . $entity->getStudyProgram()->getLetterCode() . " - " . $entity->getSemester();
//                    },
//                    'choice_value' => 'id',
//                    'label' => 'Program Course',
//                    'disabled' => $disabled
//                ])
                ->add('teacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher',
                    'disabled' => $disabled
                ])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Department',
                    'disabled' => $disabled
                ])
                ->add('courseCode', TextType::class, [
                    'required' => false,
                    'label' => 'Course Letter Code',
                    'disabled' => $disabled
                ])
                ->add('gradingType', ChoiceType::class, [
                    'attr' => [],
                    'choices' => [
                        'Gradable' => 1,
                        'Not gradable' => 0,
                    ],
                    'label' => 'Grading type',
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'disabled' => $disabled
                ])
                ->add('nameEnglish', TextType::class, [
                    'required' => false,
                    'label' => 'Name English',
                    'disabled' => $disabled
                ])
                ->add('year', TextType::class, [
                    'empty_data' => '2022',
                    'label' => 'Year',
                    'disabled' => $disabled
                ])
                ->add('semester', ChoiceType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                        '3' => 3
                    ],
                    'label' => 'Semester',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false,
                    'disabled' => $disabled
                ])
                ->add('startDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'disabled' => $disabled,
                ])
                ->add('endDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'disabled' => $disabled
                ])
                ->add('student_groups', TextType::class, [
                    'disabled' => $disabled
                ])
                ->add('student_groups_placeholder', StudentGroupsType::class, [
                    'mapped' => false
                ])
                ->add('student_groups_select', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Select groups',
                    'disabled' => $disabled
                ])
                ->add('note_note', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Note',
                    'data' => $entity->getDataField('note'),
                    'disabled' => $disabled
                ])
                ->add('note_groups', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Groups',
                    'data' => $entity->getDataField('groups'),
                    'disabled' => $disabled
                ])
                ->add('note_teacher', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Teacher',
                    'data' => $entity->getDataField('teacher'),
                    'disabled' => $disabled
                ])
                /* ->add('note_course_code', HiddenType::class, [
                  'mapped' => false,
                  'required' => false,
                  'label' => 'Note Course Code',
                  'data' => $entity->getDataField('course_code'),
                  'disabled' => $disabled
                  ])
                  ->add('note_course_name', HiddenType::class, [
                  'mapped' => false,
                  'required' => false,
                  'label' => 'Note Course name',
                  'data' => $entity->getDataField('course_name'),
                  'disabled' => $disabled
                  ]) */
                ->add('note_seminar_combined', ChoiceType::class, [
                    'mapped' => false,
                    'attr' => ['style' => 'width:100px;display:none;'],
                    'choices' => [
                        'No' => 0,
                        'Yes' => 1
                    ],
                    'label' => 'Seminar combined',
                    'data' => intval($entity->getDataField('seminar_combined')),
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('note_lecture_topics', TextareaType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Lecture topics',
                    'data' => $entity->getDataField('lecture_topics')
                ])
                ->add('note_practice_topics', TextareaType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Practice topics',
                    'data' => $entity->getDataField('practice_topics')
                ])
                ->add('note_lab_topics', TextareaType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Lab topics',
                    'data' => $entity->getDataField('lab_topics')
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
            'data_class' => TaughtCourse::class,
            'source_path' => '',
            'teacher' => null,
            'semester' => '',
            'year' => '',
            'startDate' => '',
            'endDate' => '',
            'courseTitle' => '',
            'studentGroups' => '',
            'disabledType' => 0,
            'department' => null,
        ]);
    }

}
