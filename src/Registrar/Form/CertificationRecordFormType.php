<?php

namespace App\Registrar\Form;

use App\Registrar\Entity\CertificationRecord;
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Department;
use App\Entity\Region;
use App\Entity\Country;

class CertificationRecordFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        $data = $entity->getData();
        if ($data == null) {
            $data = [
                'phone' => '',
                'university' => '',
                'university_original_name' => '',
                'study_period' => '',
                'field' => '',
                'field_original' => '',
                'qualification' => '',
                'diploma_number' => '',
                'degree' => '',
                'degree_original' => '',
                'grade1' => '',
                'grade2' => '',
                'field_local' => '',
                'diploma_number_local' => '',
                'address' => '',
                'work' => '',
                'note' => ''
            ];

            $entity->setData($data);
        }

        $builder
                ->add('registrationNumber', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Registration number'])
                ->add('protocolNumber', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Protocol number'])
                ->add('lastname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Lastname'])
                ->add('firstname', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Firstname'])
                ->add('patronym', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Patronym'])
                ->add('region', EntityType::class, ['class' => Region::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'systemId',
                    'label' => 'Region of candidate'])
                ->add('address', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Address',
                    'data' => $data['address'],
                    'required' => false
                ])
                ->add('work', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Work',
                    'data' => $data['work'],
                    'required' => false
                ])
                ->add('phone', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Phone numbers',
                    'data' => $data['phone'],
                    'required' => false
                ])
                ->add('applicationYear', IntegerType::class, [
                    'attr' => ['style' => 'width:300px;'],
                    'label' => 'Application year'
                ])
                ->add('applicationMonth', ChoiceType::class, [
                    'choices' => [
                        'February' => 2,
                        'May' => 5,
                        'September' => 9,
                        'November' => 11
                    ],
                    'label' => 'Application month'
                ])
                ->add('certificationYear', IntegerType::class, [
                    'attr' => ['style' => 'width:300px;'],
                    'label' => 'Certification year'
                ])
                ->add('certificationMonth', ChoiceType::class, [
                    'choices' => [
                        'February' => 2,
                        'May' => 5,
                        'September' => 9,
                        'November' => 11
                    ],
                    'label' => 'Certification month'
                ])
                ->add('country', EntityType::class, ['class' => Country::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'letterCode',
                    'label' => 'Country of university'])
                ->add('university', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'University Turkmen name',
                    'data' => $data['university'],
                    'required' => false
                ])
                ->add('university_original_name', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'University original name',
                    'data' => $data['university_original_name'],
                    'required' => false
                ])
                ->add('propertyType', ChoiceType::class, [
                    'choices' => [
                        'Private university' => 0,
                        'State university' => 1,
                    ]
                ])
                ->add('study_period', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Study period',
                    'data' => $data['study_period'],
                    'required' => false
                ])
                ->add('field', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Field',
                    'data' => $data['field'],
                    'required' => false
                ])
                ->add('field_original', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Field',
                    'data' => $data['field_original'],
                    'required' => false
                ])
                ->add('qualification', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Qualification',
                    'data' => $data['qualification'],
                    'required' => false
                ])
                ->add('diploma_number', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma number',
                    'data' => $data['diploma_number'],
                    'required' => false
                ])
                ->add('degree', TextType::class, [
                    'mapped' => false,
                    'label' => 'Degree',
                    'data' => $data['degree'],
                    'required' => false
                ])
                ->add('degree_original', TextType::class, [
                    'mapped' => false,
                    'label' => 'Degree Original',
                    'data' => $data['degree_original'],
                    'required' => false
                ])
                ->add('grade1', TextType::class, [
                    'mapped' => false,
                    'label' => 'Grade 1 (HZTJ)',
                    'data' => $data['grade1'],
                    'required' => false
                ])
                ->add('grade2', TextType::class, [
                    'mapped' => false,
                    'label' => 'Grade 2 (Major)',
                    'data' => $data['grade2'],
                    'required' => false
                ])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Department'
                ])
                ->add('field_local', TextType::class, [
                    'mapped' => false,
                    'label' => 'Field IUHD',
                    'data' => $data['field_local'],
                    'required' => false
                ])
                ->add('diploma_number_local', TextType::class, [
                    'mapped' => false,
                    'label' => 'Diploma number IUHD',
                    'data' => $data['diploma_number_local'],
                    'required' => false
                ])
                ->add('tags', TextType::class, [
                    'label' => 'Tags',
                    'required' => false
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Note',
                    'data' => $data['note'],
                    'required' => false
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Certified' => 1,
                        'Not certified' => 0,
                    ]
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
            'data_class' => CertificationRecord::class,
            'source_path' => ''
        ]);
    }

}
