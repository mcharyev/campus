<?php

namespace App\Form;

use App\Entity\Competition;
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

class CompetitionFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('nameEnglish', TextareaType::class, ['label' => 'Name English'])
                ->add('nameTurkmen', TextareaType::class, ['label' => 'Name Turkmen'])
                ->add('place', TextareaType::class, ['label' => 'Place'])
                ->add('organizer', TextareaType::class, ['label' => 'Organizer'])
                ->add('startDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('endDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('scope', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'sub-national' => '1',
                        'national' => '2',
                        'regional' => '3',
                        'international' => '4'
                    ],
                    'empty_data' => '2',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Scope of Competition'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'Discipline' => '1',
                        'Discipline Internet' => '2',
                        'Project' => '3'
                    ],
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Type of Competition'
                ])
                ->add('tags', TextType::class, [
                    'label' => 'Tags',
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
                    'expanded' => true,
                    'multiple' => false,
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
            'data_class' => Competition::class,
            'source_path' => ''
        ]);
    }

}
