<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\StudyProgram;
use App\Entity\Teacher;
use App\Entity\EnrolledStudent;
use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class GroupFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('letterCode')
                ->add('systemId')
                ->add('studyProgram', EntityType::class, ['class' => StudyProgram::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getLetterCode() . " - " . $entity->getNameEnglish();
                    },
                    'choice_value' => 'id',
                    'label' => 'Study Program'])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getLetterCode() . " - " . $entity->getNameEnglish();
                    },
                    'choice_value' => 'id',
                    'label' => 'Department'])
                ->add('graduationYear', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Graduation year'])
                ->add('advisor', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'systemId',
                    'label' => 'Advisor',
                    'required' => false,
                ])
                ->add('groupLeader', EntityType::class, ['class' => EnrolledStudent::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'systemId',
                    'label' => 'Group Leader',
                    'required' => false,
                ])
                ->add('scheduleName', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Schedule name', 'required' => false])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ],
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('save', SubmitType::class, ['label' => 'Create'])
                ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }

}
