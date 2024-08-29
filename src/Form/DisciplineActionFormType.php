<?php

namespace App\Form;

use App\Entity\DisciplineAction;
use App\Entity\EnrolledStudent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisciplineActionFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if ($entity->getData() == null) {
            $data = [
                'note' => ''
            ];

            $entity->setData($data);
        } else {
            $data = $entity->getData();
        }
        $builder
                ->add('student', EntityType::class, ['class' => EnrolledStudent::class,
                    'choice_label' => 'fullname',
                    'choice_value' => 'id',
                    'label' => 'Student'
                ])
                ->add('type', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        'Admonition' => '1',
                        'Reprimand' => '2',
                        'Severe reprimand' => '3',
                    ],
                    'label' => 'New session',
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false
                ])
                ->add('date', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'New date'
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Additional note',
                    'data' => $data['note'],
                    'required' => false
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
            'data_class' => DisciplineAction::class,
            'source_path' => '',
        ]);
    }

}
