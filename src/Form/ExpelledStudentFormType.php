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
use Symfony\Component\Form\CallbackTransformer;
use App\Form\Type\WorkPositionType;
use App\Form\Type\RelativeType;
use App\Entity\ExpelledStudent;
use App\Entity\ClassroomType;
use App\Entity\Country;
use App\Entity\Region;
use App\Entity\Nationality;
use App\Entity\Language;
use App\Entity\Group;

class ExpelledStudentFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //echo $entity->getGroupName();
        $data = $entity->getData();
        //$modelData = json_decode($data);
        if ($data == null) {
            //throw new TransformationFailedException('String is not a valid JSON.');
//            $modelData = json_decode("{\"name\":\"\", \"position\":\"\", \"dob\":\"\", \"nationality\":\"\", "
//                    . "\"education\":\"\", \"school\":\"\",\"profession\":\"\", \"degree\":\"\","
//                    . "\"languages\":\"\", \"awards\":\"\", \"trips\":\"\", \"mp\":\"\",\"address\":\"\", "
//                    . "\"address2\":\"\", \"phone\":\"\", \"positions\":\"[]\",\"relatives\":\"[]\"}");
            $data = [
                'expulsion_reason' => '',
                'expulsion_order' => '',
                'expulsion_date' => '',
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
                ->add('region', EntityType::class, ['class' => Region::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'systemId',
                    'label' => 'Region'])
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
                ->add('expulsion_reason', TextAreaType::class, [
                    'mapped' => false,
                    'label' => 'Expulsion reason',
                    'data' => $entity->getExpulsionReason(),
                    'required' => false,
                ])
                ->add('expulsion_date', DateType::class, [
                    'mapped' => false,
                    'label' => 'Expulsion date',
                    'data' => new \DateTime($entity->getExpulsionDate()),
                    'required' => false,
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text'
                ])
                ->add('expulsion_order', TextType::class, [
                    'mapped' => false,
                    'label' => 'Expulsion order number',
                    'data' => $entity->getExpulsionOrder(),
                    'required' => false
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
            'data' => $entity->getPosition(),
            'required' => false
        ]);

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


        //$builder->get('positions')->setData(array(['period'=>'a','position'=>'z'],['period'=>'a','position'=>'z']));
//        $builder->get('tags')
//                ->addModelTransformer(new CallbackTransformer(
//                                function ($tagsAsArray) {
//                            // transform the array to a string
//                            if ($tagsAsArray) {
//                                return implode(', ', $tagsAsArray);
//                            } else {
//                                return '';
//                            }
//                        },
//                                function ($tagsAsString) {
//                            // transform the string back to an array
//                            if (strlen($tagsAsString)) {
//                                return explode(', ', $tagsAsString);
//                            } else {
//                                return array();
//                            }
//                        }
//                ))
//        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ExpelledStudent::class,
            'source_path' => '',
        ]);
        //$resolver->setAllowedTypes('source_path', 'string');
    }

}
