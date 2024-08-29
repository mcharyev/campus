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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\TaughtCourse;
use App\Entity\Schedule;
use App\Entity\ScheduleItem;
use App\Entity\ClassType;
use App\Entity\Teacher;
use App\Entity\Classroom;

class ScheduleItemFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        $data = $entity->getData();
        if ($entity->getTaughtCourse()) {
            $courseName = $entity->getTaughtCourse()->getNameEnglish();
        } else {
            $courseName = '';
        }
        if ($entity->getTeacher()) {
            $teacherName = $entity->getTeacher()->getFullName();
            $teacherId = $entity->getTeacher()->getId();
        } else {
            $teacherName = '';
            $teacherId = 0;
        }

        if ($data == null) {
            $data = [
                'room' => '',
                'course' => $courseName,
                'groups' => '',
                'teacher' => $teacherName,
                'teacher_id' => $teacherId,
                'seminar_combined' => 0
            ];

            $entity->setData($data);
            $entity->setPeriod(1);
            $entity->setDay($options['day']);
            $entity->setSession($options['session']);
            $entity->setSchedule($options['schedule']);
            if ($options['teacher']) {
                $entity->setTeacher($options['teacher']);
            }
            if ($options['taughtCourse']) {
                $entity->setTaughtCourse($options['taughtCourse']);
                $entity->setStudentGroups($options['taughtCourse']->getStudentGroups());
            }
//            $entity->setStartDate($options['startDate']);
//            $entity->setEndDate($options['endDate']);
        }

        if (!$entity->getStartDate()) {
            $entity->setStartDate($options['startDate']);
            $entity->setEndDate($options['endDate']);
        }

        $builder
                ->add('schedule', EntityType::class, ['class' => Schedule::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Schedule'])
                ->add('year', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => $options['year'],
                ])
                ->add('semester', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => $options['semester'],
                    'help' => 'This does not matter for schedule item. For information only.'
                ])
                ->add('teacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher'])
                ->add('taughtCourse', EntityType::class, ['class' => TaughtCourse::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Taught Course'])
                ->add('studentGroups')
                ->add('source_path', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Return path',
                    'data' => $options['source_path'],
                    'required' => false
                ])
                ->add('day', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        'Monday' => '1',
                        'Tuesday' => '2',
                        'Wednesday' => '3',
                        'Thursday' => '4',
                        'Friday' => '5',
                        'Saturday' => '6',
                        'Sunday' => '7',
                    ],
                    'label' => 'Day',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('session', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                        '13' => '13',
                    ],
                    'label' => 'Session',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('period', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ],
                    'label' => 'Period',
                    'empty_data' => '1',
                    'help' => 'indicates repetition in every n weeks',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('classType', EntityType::class, ['class' => ClassType::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Class Type',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('startDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'disabled' => false
                ])
                ->add('endDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'disabled' => false
                ])
                ->add('note_seminar_combined', ChoiceType::class, [
                    'mapped' => false,
                    'attr' => ['style' => 'width:100px;'],
                    'choices' => [
                        'No' => 0,
                        'Yes' => 1
                    ],
                    'label' => 'Seminar combined',
                    'data' => intval($entity->getDataField('seminar_combined')),
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('rooms')
                ->add('classRoombox', EntityType::class, ['class' => Classroom::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getLetterCode() . " - " . $entity->getNameEnglish();
                    },
                    'choice_value' => 'id',
                    'label' => 'Select Classroom',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('note_teacher', TextType::class, [
                    'label' => 'Fulfiller',
                    'mapped' => false,
                    'required' => false,
                    'data' => $entity->getDataField("teacher")
                ])
                ->add('note_course_name', TextType::class, [
                    'label' => 'Course name',
                    'mapped' => false,
                    'required' => false,
                    'data' => $entity->getNoteCourseName()
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
            'data_class' => ScheduleItem::class,
            'source_path' => '',
            'year' => '',
            'semester' => '',
            'teacher' => null,
            'taughtCourse' => null,
            'day' => 1,
            'session' => 1,
            'startDate' => null,
            'endDate' => null,
            'schedule' => null,
        ]);
    }

}
