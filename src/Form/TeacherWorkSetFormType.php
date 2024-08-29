<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\TeacherWorkSet;
use App\Entity\Department;
use App\Enum\WorkloadEnum;
use App\Entity\Teacher;
use App\Service\SystemInfoManager;
use App\Controller\Workload\WorkColumnsArray;

class TeacherWorkSetFormType extends AbstractType {

    private $systemInfoManager;
    private $workColumnsManager;

    public function __construct(SystemInfoManager $systemInfoManager, WorkColumnsArray $workColumnsManager) {
        $this->systemInfoManager = $systemInfoManager;
        $this->workColumnsManager = $workColumnsManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //echo $entity->getGroupName();
        $data = $entity->getData();
        //$modelData = json_decode($data);
        if ($data == null) {
            //throw new TransformationFailedException('String is not a valid JSON.');
            $data = [
                'note' => '',
//                'semestersum1' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
//                'semestersum2' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
//                'semestersum3' => $this->workColumnsManager->getEmptyWorkColumnsArray(),
            ];

            $entity->setData($data);
            if (!empty($options['year'])) {
                $entity->setYear($options['year']);
            }
            if (!empty($options['department'])) {
                $entity->setDepartment($options['department']);
                if ($entity->getStartDate() == null) {
                    if ($options['department']->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                        $entity->setStartDate($this->systemInfoManager->getTrimesterBeginDate($this->systemInfoManager->getCurrentTrimester()));
                    } else {
                        $entity->setStartDate($this->systemInfoManager->getSemesterBeginDate($this->systemInfoManager->getCurrentSemester()));
                    }
                }
                if ($entity->getEndDate() == null) {
                    if ($options['department']->getSystemId() == $this->systemInfoManager->getLLDSystemId()) {
                        $entity->setEndDate($this->systemInfoManager->getTrimesterEndDate($this->systemInfoManager->getCurrentTrimester()));
                    } else {
                        $entity->setEndDate($this->systemInfoManager->getSemesterEndDate($this->systemInfoManager->getCurrentSemester()));
                    }
                }
            }
            if (!empty($options['teacher'])) {
                $entity->setTeacher($options['teacher']);
            }
            if (!empty($options['workload'])) {
                $entity->setWorkload($options['workload']);
            }
            $entity->setViewOrder(1);
        }


//        if($entity->getDataField('semestersum1')=="")
//        {
//            $entity->setDataField('semestersum1',$this->workColumnsManager->getEmptyWorkColumnsArray());
//            $entity->setDataField('semestersum2',$this->workColumnsManager->getEmptyWorkColumnsArray());
//            $entity->setDataField('semestersum3',$this->workColumnsManager->getEmptyWorkColumnsArray());
//        }


        $builder
                ->add('teacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher'])
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
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'data' => $entity->getDepartment(),
                    'label' => 'Department'])
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
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Note',
                    'data' => $entity->getNote('note'),
                    'help' => 'Use this field to differentiate the work set from others',
                    'required' => false
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
            'data_class' => TeacherWorkSet::class,
            'source_path' => '',
            'teacher' => null,
            'year' => 2019,
            'workload' => 0,
            'department' => null
        ]);
        //$resolver->setAllowedTypes('source_path', 'string');
    }

}
