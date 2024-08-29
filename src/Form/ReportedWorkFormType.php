<?php

namespace App\Form;

use App\Entity\ReportedWork;
use App\Entity\Teacher;
use App\Entity\TeacherWorkItem;
use App\Entity\User;
use App\Enum\WorkColumnEnum;
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
use Symfony\Component\Form\CallbackTransformer;

class ReportedWorkFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if ($entity->getData() == null) {
            $data = [
                'note' => ''
            ];
            $entity->setTeacher($options['teacherWorkItem']->getTeacher());
            $entity->setWorkitem($options['teacherWorkItem']);
            $entity->setData($data);
        } else {
            $data = $entity->getData();
        }
        $builder
                ->add('teacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher',
                    'disabled' => !$options['teacherEnabled'],
                ])
                //->add('author')
                ->add('workitem', EntityType::class, ['class' => TeacherWorkItem::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getTeacher()->getShortFullname() . " - " . $entity->getTitle() . " - " . $entity->getStudentGroups() . " - " . $entity->getId();
                    },
                    'choice_value' => 'id',
                    'label' => 'Teacher work item',
                    'disabled' => !$options['workItemEnabled'],
                    'help' => "Teacher name - work item name - student groups - work item id",
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        WorkColumnEnum::getTypeName(WorkColumnEnum::CONSULTATION) => WorkColumnEnum::CONSULTATION,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::NONCREDIT) => WorkColumnEnum::NONCREDIT,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::MIDTERMEXAM) => WorkColumnEnum::MIDTERMEXAM,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::FINALEXAM) => WorkColumnEnum::FINALEXAM,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::STATEEXAM) => WorkColumnEnum::STATEEXAM,
                        //WorkColumnEnum::getTypeName(WorkColumnEnum::SIWSI) => WorkColumnEnum::SIWSI,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::CUSW) => WorkColumnEnum::CUSW,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::INTERNSHIP) => WorkColumnEnum::INTERNSHIP,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::THESISADVISING) => WorkColumnEnum::THESISADVISING,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::THESISEXAM) => WorkColumnEnum::THESISEXAM,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::COMPETITION) => WorkColumnEnum::COMPETITION,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::CLASSOBSERVATION) => WorkColumnEnum::CLASSOBSERVATION,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::ADMINISTRATION) => WorkColumnEnum::ADMINISTRATION,
                        WorkColumnEnum::getTypeName(WorkColumnEnum::THESISREVIEW) => WorkColumnEnum::THESISREVIEW,
                    ],
                    'data' => $entity->getType(),
                    'expanded' => false,
                    'multiple' => false
                ])
                ->add('amount', IntegerType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Amount',
                    'empty_data' => '0'
                ])
                ->add('date', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Additional note',
                    'data' => $data['note'],
                    'required' => false
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => '1',
                        'Disabled' => '0',
                    ],
                    'empty_data' => '1',
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
            'data_class' => ReportedWork::class,
            'source_path' => '',
            'teacherWorkItem' => null,
            'teacherEnabled' => false,
            'workItemEnabled' => false,
            'year' => null,
            'semester' => 1,
        ]);
    }

}
