<?php

namespace App\Form;

use App\Entity\SystemEvent;
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

class SystemEventFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('type')
                ->add('subjectType', TextType::class, [
                    'empty_data' => '',
                    'label' => 'Subject type'
                ])
                ->add('subjectId', TextType::class, [
                    'empty_data' => '',
                    'label' => 'Subject Id'
                ])
                 ->add('objectType', TextType::class, [
                    'empty_data' => '',
                    'label' => 'Object type'
                ])
                ->add('objectId', TextType::class, [
                    'empty_data' => '',
                    'label' => 'Object Id'
                ])
                ->add('data', TextType::class, [
                    'empty_data' => '',
                    'label' => 'Data'
                ])
                ->add('dateUpdated', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
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
            'data_class' => SystemEvent::class,
            'source_path' => ''
        ]);
    }

}
