<?php

namespace App\Form;

use App\Entity\Announcement;
use App\Entity\Department;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class AnnouncementFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('title', TextType::class, ['label' => 'Title'])
                ->add('content', TextareaType::class, ['label' => 'Lastname'])
                ->add('category', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'Instructors' => '1',
                        'Students' => '2',
                        'All' => '3'
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Category'
                ])
                ->add('dateExpiry', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
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
                ->add('author', EntityType::class, ['class' => User::class,
                    'choice_label' => 'username',
                    'choice_value' => 'id',
                    'disabled' => false,
                    'label' => 'Author'
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
            'data_class' => Announcement::class,
            'source_path' => ''
        ]);
    }

}
