<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\StudentGroupsType;
use App\Entity\DepartmentWorkItem;
use App\Entity\TeacherWorkItem;
use App\Entity\TeacherWorkSet;
use App\Entity\Department;
use App\Entity\TaughtCourse;
use App\Entity\Group;
use App\Enum\WorkColumnEnum;
use App\Enum\WorkRowEnum;
use App\Enum\WorkloadEnum;
use App\Entity\Teacher;

class TeacherWorkItemFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //echo $entity->getGroupName();
        $data = $entity->getData();
        //$modelData = json_decode($data);
        if ($data == null) {
            //throw new TransformationFailedException('String is not a valid JSON.');
            $data = [
                WorkColumnEnum::TITLE => '',
                WorkColumnEnum::GROUPNAMES => '',
                WorkColumnEnum::STUDYYEAR => 0,
                WorkColumnEnum::STUDENTCOUNT => 0,
                WorkColumnEnum::COHORT => 1,
                WorkColumnEnum::GROUPS => 1,
                WorkColumnEnum::SUBGROUPS => 1,
                WorkColumnEnum::LECTUREPLAN => 0,
                WorkColumnEnum::LECTUREACTUAL => 0,
                WorkColumnEnum::PRACTICEPLAN => 0,
                WorkColumnEnum::PRACTICEACTUAL => 0,
                WorkColumnEnum::LABPLAN => 0,
                WorkColumnEnum::LABACTUAL => 0,
                WorkColumnEnum::CONSULTATION => 0,
                WorkColumnEnum::NONCREDIT => 0,
                WorkColumnEnum::MIDTERMEXAM => 0,
                WorkColumnEnum::FINALEXAM => 0,
                WorkColumnEnum::STATEEXAM => 0,
                WorkColumnEnum::SIWSI => 0,
                WorkColumnEnum::CUSW => 0,
                WorkColumnEnum::INTERNSHIP => 0,
                WorkColumnEnum::THESISADVISING => 0,
                WorkColumnEnum::THESISEXAM => 0,
                WorkColumnEnum::COMPETITION => 0,
                WorkColumnEnum::CLASSOBSERVATION => 0,
                WorkColumnEnum::ADMINISTRATION => 0,
                WorkColumnEnum::THESISREVIEW => 0,
                WorkColumnEnum::TOTAL => 0,
                'includeColumn' => 0
            ];

            $entity->setData($data);
            if (!empty($options['year'])) {
                $entity->setYear($options['year']);
            }
            if (!empty($options['semester'])) {
                $entity->setSemester($options['semester']);
            }
            if (!empty($options['teacher'])) {
                $entity->setTeacher($options['teacher']);
            }
            if (!empty($options['workload'])) {
                $entity->setWorkload($options['workload']);
            }
             if (!empty($options['teacherWorkSet'])) {
                $entity->setTeacherWorkSet($options['teacherWorkSet']);
            }
            $entity->setTaughtCourse(null);

            $entity->setStudentCount(1);
            $entity->setViewOrder(1);
        }

        if ($entity->getTaughtCourse()) {
            $taughtCourse = $entity->getTaughtCourse()->getId();
        } else {
            $taughtCourse = 0;
        }

        if ($entity->getDepartmentWorkItem()) {
            $originalDepartmentWorkItem = $entity->getDepartmentWorkItem()->getId();
        } else {
            $originalDepartmentWorkItem = 0;
        }
        $originalIncludeColumn = 0;
        if ($entity->getDataField('includeColumn')) {
            $originalIncludeColumn = intval($entity->getDataField('includeColumn'));
        }

        $builder
                ->add('teacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher'])
                ->add('teacherWorkSet', EntityType::class, ['class' => TeacherWorkSet::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getTitle();
                    },
                    'required' => false,
                    'choice_value' => 'id',
                    'label' => 'Teacher work set'])
                ->add('workload', ChoiceType::class, [
                    'choices' => [
                        WorkloadEnum::getTypeName(WorkloadEnum::LOAD100) => WorkloadEnum::LOAD100,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOAD075) => WorkloadEnum::LOAD075,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOAD050) => WorkloadEnum::LOAD050,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOAD025) => WorkloadEnum::LOAD025,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOADOTHER) => WorkloadEnum::LOADOTHER,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOADHOURLY) => WorkloadEnum::LOADHOURLY,
                        WorkloadEnum::getTypeName(WorkloadEnum::LOADREPLACEMENT) => WorkloadEnum::LOADREPLACEMENT,
                    ],
                    'data' => $entity->getWorkload(),
                    'expanded' => false,
                    'multiple' => false
                ])
                ->add('year', IntegerType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Year',
                    'empty_data' => '0',
                    'data' => $entity->getYear(),
                ])
                ->add('semester', ChoiceType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                    ],
                    'data' => $entity->getSemester(),
                    'empty_data' => 1,
                    'expanded' => false,
                    'multiple' => false
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        WorkRowEnum::getTypeName(WorkRowEnum::CREDITCOURSE) => WorkRowEnum::CREDITCOURSE,
                        WorkRowEnum::getTypeName(WorkRowEnum::NONCREDITCOURSE) => WorkRowEnum::NONCREDITCOURSE,
                        WorkRowEnum::getTypeName(WorkRowEnum::COURSEEXAM) => WorkRowEnum::COURSEEXAM,
                        WorkRowEnum::getTypeName(WorkRowEnum::INTERNSHIPCOURSE) => WorkRowEnum::INTERNSHIPCOURSE,
                        WorkRowEnum::getTypeName(WorkRowEnum::THESISADVISING) => WorkRowEnum::THESISADVISING,
                        WorkRowEnum::getTypeName(WorkRowEnum::THESISEXAM) => WorkRowEnum::THESISEXAM,
                        WorkRowEnum::getTypeName(WorkRowEnum::STATEEXAM) => WorkRowEnum::STATEEXAM,
                        WorkRowEnum::getTypeName(WorkRowEnum::COMPETITION) => WorkRowEnum::COMPETITION,
                        WorkRowEnum::getTypeName(WorkRowEnum::CLASSOBSERVATION) => WorkRowEnum::CLASSOBSERVATION,
                        WorkRowEnum::getTypeName(WorkRowEnum::ADMINISTRATION) => WorkRowEnum::ADMINISTRATION,
                        WorkRowEnum::getTypeName(WorkRowEnum::THESISREVIEW) => WorkRowEnum::THESISREVIEW,
                    ],
                    'data' => $entity->getType(),
                    'expanded' => false,
                    'multiple' => false
                ])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'data' => $options['department'],
                    'label' => 'Department'])
                ->add('departmentWorkItem', EntityType::class, ['class' => DepartmentWorkItem::class,
                    'choice_label' => function($entity = null) {
//                    "[".$entity->getId()."] ".
                        return $entity->getTitle() . " " . $entity->getGroupNames();
                    },
                    'choice_value' => 'id',
                    'label' => 'Department work item'
                ])
                ->add('departmentWorkItemOriginal', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Original department work item',
                    'data' => $originalDepartmentWorkItem,
                    'required' => false
                ])
                ->add('title', TextType::class, [
                    'label' => 'Title'
                ])
//                ->add('taughtCourse', ChoiceType::class, [
//                    'choices' => [0 => 'No course'],
//                    'data' => $taughtCourse,
//                    'expanded' => false,
//                    'multiple' => false
//                ])
                ->add('taughtCourse', EntityType::class, ['class' => TaughtCourse::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Taught Course'
                ])
                ->add('taughtCourseOriginal', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Original taught course',
                    'data' => $taughtCourse,
                    'required' => false
                ])
                ->add('studentCount', IntegerType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Student count',
                    'empty_data' => '0'
                ])
                ->add('student_groups', TextType::class, [
                    'required' => false,
                    'label' => 'Groups',
                    'data' => ''
                ])
                ->add('student_groups_placeholder', StudentGroupsType::class, [
                    'mapped' => false
                ])
                ->add('student_groups_select', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Select groups'
                ])
                ->add('includeColumn', ChoiceType::class, [
                    'mapped' => false,
                    'attr' => ['style' => 'width:200px;'],
                    'choices' => [
                        'All' => 0,
                        'Lecture only' => 1,
                        'Practice only' => 2,
                        'Lab only' => 3,
                        'Exams only' => 4,
                    ],
                    'data' => $originalIncludeColumn,
                    'expanded' => false,
                    'multiple' => false
                ])
                ->add('customGroupCount', IntegerType::class, [
                    'mapped' => false,
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Custom group count',
                    'empty_data' => '0',
                    'data' => $entity->getGroupCount(),
                ])
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
                    'expanded' => false,
                    'multiple' => false
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
            'data_class' => TeacherWorkItem::class,
            'source_path' => '',
            'teacher' => null,
            'year' => 2022,
            'semester' => 1,
            'workload' => 0,
            'department' => null,
            'teacherWorkSet' => null,
        ]);
        //$resolver->setAllowedTypes('source_path', 'string');
    }

}
