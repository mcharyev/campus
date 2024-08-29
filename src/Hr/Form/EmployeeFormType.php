<?php

namespace App\Hr\Form;

use App\Hr\Entity\Employee;
use App\Entity\Nationality;
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
use App\Form\Type\WorkPositionType;
use App\Form\Type\RelativeType;

class EmployeeFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //echo $entity->getGroupName();
        $data = $entity->getData();
        //$modelData = json_decode($data);
        if ($data == null) {
            $data = [
                'name' => '',
                'position' => '',
                'dob' => '',
                'nationality' => '',
                'education' => '',
                'school' => '',
                'profession' => '',
                'degree' => '',
                'languages' => '',
                'awards' => '',
                'trips' => '',
                'mp' => '',
                'address' => '',
                'address2' => '',
                'phone' => '',
                'positions' => json_encode([]),
                'relatives' => json_encode([])
            ];

            $entity->setData($data);
        }

        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('firstname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname'])
                ->add('lastname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname'])
                ->add('patronym', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Patronym', 'required' => false])
                ->add('previousLastname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Previous Lastname', 'required' => false])
                ->add('level', ChoiceType::class, [
                    'choices' => [
                        'Işgär' => '0',
                        'Garawul' => '1',
                        'Başlyk' => '2',
                        'Dekan' => '3',
                        'Admin' => '4'
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Level'
                ])
                ->add('worktimeCategory', TextType::class, [
                    'label' => 'Worktime Category',
                    'required' => true,
                    'empty_data' => 0,
                ])
                ->add('employmentDate', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('position', TextareaType::class, [
                    'label' => 'Position',
                    'required' => false
                ])
                ->add('experience', TextType::class, [
                    'label' => 'Experience',
                    'required' => false
                ])
                ->add('birthdate', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('birthplace', TextareaType::class, [
                    'label' => 'Birthplace',
                    'required' => false
                ])
                ->add('address', TextareaType::class, [
                    'label' => 'Address',
                    'required' => false
                ])
                ->add('permanentAddress', TextareaType::class, [
                    'label' => 'Permanent address',
                    'required' => true
                ])
                ->add('phone1', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Phone 1', 'required' => false])->add('phone1')
                ->add('phone2', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Phone 2', 'required' => false])->add('phone2')
                ->add('phone3', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Phone 3', 'required' => false])->add('phone3')
                ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'Male' => 1,
                        'Female' => 0,
                    ]
                ])
                ->add('maritalStatus', ChoiceType::class, [
                    'choices' => [
                        'Married' => 1,
                        'Not married' => 0,
                    ]
                ])
                ->add('Education', TextareaType::class, [
                    'label' => 'Education',
                    'required' => false
                ])
                ->add('zdno', TextType::class, [
                    'label' => 'Zähmet depderçesiniň belgisi',
                    'required' => false
                ])
                ->add('zdno', TextType::class, [
                    'label' => 'ŞTU belgisi',
                    'required' => false
                ])
                ->add('pension', TextareaType::class, [
                    'label' => 'Pension',
                    'required' => false
                ])
                ->add('passport', TextareaType::class, [
                    'label' => 'Passport',
                    'required' => false
                ])
                ->add('passportId', TextType::class, [
                    'label' => 'Passport ID',
                    'required' => false
                ])
                ->add('nationality', EntityType::class, ['class' => Nationality::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'systemId',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Nationality'])
                ->add('scientificDegree', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'bachelor' => '1',
                        'specialist' => '2',
                        'masters' => '3',
                        'PhD' => '4',
                        'Cand.Sci.' => '5'
                    ],
                    'empty_data' => '0',
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Academic Degree'
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

        if (strlen($entity->getDataField('name')) > 0) {
            $fullname = $entity->getDataField('name');
        } else {
            $fullname = $entity->getLastname() . " " . $entity->getFirstname() . " " . $entity->getPatronym();
        }
        $builder->add('info_name', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Full name',
                    'data' => $fullname,
                    'required' => false
                ])
                ->add('info_position', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Position',
                    'data' => $entity->getDataField('position'),
                    'required' => false
                ])
                ->add('info_dob', TextType::class, [
                    'mapped' => false,
                    'label' => 'Date of birth, place',
                    'data' => $entity->getDataField('dob'),
                    'required' => false
                ])
                ->add('info_nationality', TextType::class, [
                    'mapped' => false,
                    'label' => 'Nationality',
                    'data' => $entity->getDataField('nationality'),
                    'required' => false])
                ->add('info_education', TextType::class, [
                    'mapped' => false,
                    'label' => 'Education',
                    'data' => $entity->getDataField('education'),
                    'required' => false])
                ->add('info_school', TextType::class, [
                    'mapped' => false,
                    'label' => 'School',
                    'data' => $entity->getDataField('school'),
                    'required' => false])
                ->add('info_profession', TextType::class, [
                    'mapped' => false,
                    'label' => 'Profession',
                    'data' => $entity->getDataField('profession'),
                    'required' => false])
                ->add('info_degree', TextType::class, [
                    'mapped' => false,
                    'label' => 'Degree',
                    'data' => $entity->getDataField('degree'),
                    'required' => false])
                ->add('info_languages', TextType::class, [
                    'mapped' => false,
                    'label' => 'Languages',
                    'data' => $entity->getDataField('languages'),
                    'required' => false])
                ->add('info_awards', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Awards',
                    'data' => $entity->getDataField('awards'),
                    'required' => false])
                ->add('info_trips', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Trips',
                    'data' => $entity->getDataField('trips'),
                    'required' => false])
                ->add('info_mp', TextType::class, [
                    'mapped' => false,
                    'label' => 'Member of Parliament',
                    'data' => $entity->getDataField('mp'),
                    'required' => false])
                ->add('info_address', TextType::class, [
                    'mapped' => false,
                    'label' => 'Address',
                    'data' => $entity->getDataField('address'),
                    'required' => false])
                ->add('info_address2', TextType::class, [
                    'mapped' => false,
                    'label' => 'Address',
                    'data' => $entity->getDataField('address2'),
                    'required' => false])
                ->add('info_phone', TextType::class, [
                    'mapped' => false,
                    'label' => 'Phone',
                    'data' => $entity->getDataField('phone'),
                    'required' => false])
        ;


        $builder->add('info_positions', CollectionType::class, [
            'entry_type' => WorkPositionType::class,
            'entry_options' => [
                'attr' => ['class' => 'email-box'],
            ],
            'data' => $entity->getPositions(),
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'prototype' => true
        ]);

        $builder->add('info_relatives', CollectionType::class, [
            'entry_type' => RelativeType::class,
            'entry_options' => [
                'attr' => ['class' => 'email-box'],
            ],
            'data' => $entity->getRelatives(),
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'prototype' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Employee::class,
            'source_path' => ''
        ]);
    }

}
