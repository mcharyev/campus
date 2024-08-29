<?php

namespace App\Form;

use App\Entity\ScheduleChange;
use App\Entity\TaughtCourse;
use App\Entity\ClassType;
use App\Entity\Teacher;
use App\Entity\ScheduleItem;
use App\Entity\Classroom;
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

class ScheduleChangeFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if ($entity->getData() == null) {
            $data = [
                'note' => ''
            ];
            $entity->setStatus(0);

            if ($options['scheduleItem']) {
                $entity->setScheduleItem($options['scheduleItem']);
                $entity->setNewTeacher($options['scheduleItem']->getTeacher());
                $entity->setClassType($options['scheduleItem']->getClassType());
                $entity->setSession($options['scheduleItem']->getSession());
                $entity->setNewSession($options['scheduleItem']->getSession());
            }
            $entity->setData($data);
            $entity->setYear($options['year']);
            $entity->setSemester($options['semester']);
            if ($options['date']) {
                $entity->setDate(new \DateTime($options['date']));
            }
        } else {
            $data = $entity->getData();
        }
        $builder
                ->add('scheduleItem', EntityType::class, ['class' => ScheduleItem::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getTaughtCourse()->getFullname() . " Schedule Item Id:" . $entity->getId();
                    },
                    'choice_value' => 'id',
                    'label' => 'Schedule Item'])
                ->add('year', TextType::class, [
                    'label' => 'Year',
                    'attr' => ['style' => 'width:150px;'],
                ])
                ->add('semester', TextType::class, [
                    'label' => 'Semester',
                    'attr' => ['style' => 'width:150px;'],
                ])
                ->add('date', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
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
                    ],
                    'label' => 'Session',
                    'expanded' => true,
                    'empty_data' => '1',
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
                ->add('newDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'New date'
                ])
                ->add('newSession', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                    ],
                    'label' => 'New session',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('newTeacher', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'New teacher'])
                ->add('classroom', EntityType::class, ['class' => Classroom::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getLetterCode() . " " . $entity->getNameEnglish();
                    },
                    'choice_value' => 'id',
                    'label' => 'New classroom'])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Additional note',
                    'data' => $entity->getDataField('note'),
                    'required' => false
                ])
                ->add('status', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        'Unlocked' => '0',
                        'Locked' => '1',
                    ],
                    'label' => 'Status',
                    'expanded' => true,
                    'empty_data' => '0',
                    'multiple' => false,
                    'disabled' => true,
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
            'data_class' => ScheduleChange::class,
            'source_path' => '',
            'scheduleItem' => null,
            'year' => null,
            'semester' => 1,
            'date' => null,
        ]);
    }

}
