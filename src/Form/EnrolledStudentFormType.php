<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\WorkPositionType;
use App\Form\Type\RelativeType;
use App\Entity\EnrolledStudent;
use App\Entity\Country;
use App\Entity\Region;
use App\Entity\HostelRoom;
use App\Entity\Nationality;
use App\Entity\Group;

class EnrolledStudentFormType extends AbstractType {

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
                'permanent_address' => '',
                'temporary_registration_address' => '',
                'address2' => '',
                'passport' => '',
                'phone' => '',
                'mobile_phone' => '',
                'positions' => json_encode([]),
                'relatives' => json_encode([]),
                'thesis' => '',
                'thesis_advisor' => '',
                'internship_place' => '',
                'internship_advisor' => '',
                'diploma_registration_number' => '',
                'diploma_type' => '',
                'diploma_number' => '',
                'diploma_order_date' => '',
                'diploma_receive_date' => '',
                'diploma_note' => '',
                'diploma_chairman' => '',
                'employment_place' => '',
                'employment_position' => '',
                'employment_note' => '',
            ];

            $entity->setData($data);
        }
        if ($data) {
            if ($entity->getStudentGroup()) {
                $studyProgram = $entity->getStudentGroup()->getStudyProgram();
                $department = $studyProgram->getDepartment();
                $faculty = $department->getFaculty();
                if ($entity->getStudentGroup()->getGraduationYear() == 2024) {
                    $entity->setDataField('position', 'Halkara ynsanperwer ylymlary we ösüş uniwersitetiniň ' . $faculty->getNameTurkmen() . ' fakultetiniň ' . $studyProgram->getNameTurkmen() . ' hünäriniň 1-nji ýyl talyby');
                } else {
                    $entity->setDataField('position', $entity->getCurrentPosition());
                }
            }
        }

        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('lastnameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname Turkmen'])
                ->add('firstnameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname Turkmen'])
                ->add('patronymTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Patronym Turkmen', 'required' => false])
                ->add('previousLastnameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Previous Lastname Turkmen', 'required' => false])
                ->add('lastnameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname English', 'required' => false])
                ->add('firstnameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname English', 'required' => false])
                ->add('patronymEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Patronym English', 'required' => false])
                ->add('previousLastnameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Previous Lastname English', 'required' => false])
                ->add('birthDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('matriculationDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'required'=> false
                ])
                ->add('graduationDate', DateType::Class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'required'=>false
                ])
                ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'Male' => 1,
                        'Female' => 0,
                    ]
                ])
                ->add('subgroup', ChoiceType::class, [
                    'choices' => [
                        'A' => 1,
                        'B' => 2,
                    ]
                ])
                ->add('nationalId', TextareaType::class, [
                    'mapped' => true,
                    'label' => "Passport information",
                    'required' => false,
                    'data' => $entity->getNationalId()
                ])
                ->add('region', EntityType::class, ['class' => Region::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'systemId',
                    'label' => 'Region'])
                ->add('address', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Permanent Registration Address',
                    'required' => false,
                    'data' => $entity->getDataField('address')
                ])
                ->add('permanent_address', TextareaType::class, [
                    'mapped' => false,
                    'label' => "Permanent Address",
                    'required' => false,
                    'data' => $entity->getDataField('permanent_address')
                ])
                ->add('temporary_registration_address', TextareaType::class, [
                    'mapped' => false,
                    'label' => "Temporary Registration Address",
                    'required' => false,
                    'data' => $entity->getDataField('temporary_registration_address')
                ])
                ->add('address2', TextareaType::class, [
                    'mapped' => false,
                    'label' => "Temporary Address",
                    'required' => false,
                    'data' => $entity->getDataField('address2')
                ])
                ->add('phone', TextareaType::class, [
                    'mapped' => false,
                    'label' => "Home phone",
                    'required' => false,
                    'data' => $entity->getDataField('phone')
                ])
                ->add('mobile_phone', TextareaType::class, [
                    'mapped' => false,
                    'label' => "Mobile phone",
                    'required' => false,
                    'data' => $entity->getDataField('mobile_phone')
                ])
                ->add('hostelRoom', EntityType::class, ['class' => HostelRoom::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'required' => false,
                    'label' => 'Hostel Room'])
                ->add('country', EntityType::class, ['class' => Country::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'letterCode',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Citizenship'])
                ->add('nationality', EntityType::class, ['class' => Nationality::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'systemId',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Nationality'])
                ->add('maritalStatus', ChoiceType::class, [
                    'choices' => [
                        'Married' => 1,
                        'Not married' => 0,
                    ]
                ])
                ->add('studentGroup', EntityType::class, ['class' => Group::class,
                    'choice_label' => 'letterCode',
                    'choice_value' => 'systemId',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Group'])
                ->add('groupCode', TextType::class, [
                    'label' => 'Group Code',
                ])
                ->add('tags', TextType::class, [
                    'label' => 'Tags',
                    'required' => false
                ])
                ->add('thesis', TextType::class, [
                    'mapped' => false,
                    'label' => 'Thesis',
                    'required' => false,
                    'data' => $entity->getDataField('thesis')
                ])
                ->add('thesis_advisor', TextType::class, [
                    'mapped' => false,
                    'label' => 'Thesis supervisor',
                    'required' => false,
                    'data' => $entity->getDataField('thesis_advisor')
                ])
                ->add('internship_place', TextType::class, [
                    'mapped' => false,
                    'label' => 'Internship place',
                    'required' => false,
                    'data' => $entity->getDataField('internship_place')
                ])
                ->add('internship_advisor', TextType::class, [
                    'mapped' => false,
                    'label' => 'Internship supervisor',
                    'required' => false,
                    'data' => $entity->getDataField('internship_advisor')
                ])
                ->add('diploma_registration_number', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma registration number',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_registration_number')
                ])
                ->add('diploma_type', ChoiceType::class, [
                    'choices' => [
                        'Ordinary' => 0,
                        'With distinction' => 1,
                    ],
                    'mapped' => false,
                    'data' => $entity->getDataField('diploma_type')
                ])
                ->add('diploma_number', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma number',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_number')
                ])
                ->add('diploma_order_date', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma order date',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_order_date')
                ])
                ->add('diploma_receive_date', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma receive date',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_receive_date')
                ])
                ->add('diploma_chairman', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma chairman',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_chairman')
                ])
                ->add('diploma_note', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma note',
                    'required' => false,
                    'data' => $entity->getDataField('diploma_note')
                ])
                ->add('employment_place', TextType::class, [
                    'mapped' => false,
                    'label' => 'Employment place',
                    'required' => false,
                    'data' => $entity->getDataField('employment_place')
                ])
                ->add('employment_position', TextType::class, [
                    'mapped' => false,
                    'label' => 'Employment position',
                    'required' => false,
                    'data' => $entity->getDataField('employment_position')
                ])
                ->add('employment_note', TextType::class, [
                    'mapped' => false,
                    'label' => 'Employment note',
                    'required' => false,
                    'data' => $entity->getDataField('employment_note')
                ])
                ->add('source_path', HiddenType::class, [
                    'mapped' => false,
                    'label' => 'Return path',
                    'data' => $options['source_path'],
                    'required' => false
                ])
        ;

        $builder->add('position', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Position',
                    'data' => $entity->getDataField('position'),
                    'required' => false
                ])
                ->add('dob', TextType::class, [
                    'mapped' => false,
                    'label' => 'Date of birth, place',
                    'data' => $entity->getDataField('dob'),
                    'required' => false
                ])
                ->add('education', TextType::class, [
                    'mapped' => false,
                    'label' => 'Education',
                    'data' => $entity->getDataField('education'),
                    'required' => false])
                ->add('school', TextType::class, [
                    'mapped' => false,
                    'label' => 'School',
                    'data' => $entity->getDataField('school'),
                    'required' => false])
                ->add('profession', TextType::class, [
                    'mapped' => false,
                    'label' => 'Profession',
                    'data' => $entity->getDataField('profession'),
                    'required' => false])
                ->add('degree', TextType::class, [
                    'mapped' => false,
                    'label' => 'Degree',
                    'data' => $entity->getDataField('degree'),
                    'required' => false])
                ->add('languages', TextType::class, [
                    'mapped' => false,
                    'label' => 'Languages',
                    'data' => $entity->getDataField('languages'),
                    'required' => false])
                ->add('awards', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Awards',
                    'data' => $entity->getDataField('awards'),
                    'required' => false])
                ->add('trips', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Trips',
                    'data' => $entity->getDataField('trips'),
                    'required' => false])
                ->add('mp', TextType::class, [
                    'mapped' => false,
                    'label' => 'Member of Parliament',
                    'data' => $entity->getDataField('mp'),
                    'required' => false])
//                ->add('address', TextType::class, [
//                    'mapped' => false,
//                    'label' => 'Address',
//                    'data' => $entity->getDataField('address'),
//                    'required' => false])
//                ->add('address2', TextType::class, [
//                    'mapped' => false,
//                    'label' => 'Address',
//                    'data' => $entity->getDataField('address2'),
//                    'required' => false])
//                ->add('phone', TextType::class, [
//                    'mapped' => false,
//                    'label' => 'Phone',
//                    'data' => $entity->getDataField('phone'),
//                    'required' => false])
        ;


        $builder->add('positions', CollectionType::class, [
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

        $builder->add('relatives', CollectionType::class, [
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
            'data_class' => EnrolledStudent::class,
            'source_path' => '',
        ]);
        //$resolver->setAllowedTypes('source_path', 'string');
    }

}
