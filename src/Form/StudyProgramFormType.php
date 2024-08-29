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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use App\Entity\StudyProgram;
use App\Entity\ProgramLevel;
use App\Entity\Department;

class StudyProgramFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        $data = $entity->getData();
        if($data==null)
        {
            $data = [
                'field' => '',
                'subfield' => '',
                'major' => '',
                'degree' => '',
                'qualification' => '',
                'deputy' => '',
                'minister' => '',
                'rector' => '',
                'date' => ''
            ];
            $entity->setData($data);
        }

        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name Turkmen'])
                ->add('approvalYear', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Approval year'])
                ->add('programLevel', EntityType::class, ['class' => ProgramLevel::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Program level'])
                ->add('department', EntityType::class, ['class' => Department::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Department'])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ]
                ])
                ->add('field', TextType::class, [
                    'mapped' => false,
                    'label' => 'Field',
                    'data' => $entity->getField(),
                    'required' => false
                ])
                ->add('subfield', TextType::class, [
                    'mapped' => false,
                    'label' => 'Subfield',
                    'data' => $entity->getSubfield(),
                    'required' => false
                ])
                ->add('major', TextType::class, [
                    'mapped' => false,
                    'label' => 'Major',
                    'data' => $entity->getMajor(),
                    'required' => false
                ])
                ->add('degree', TextType::class, [
                    'mapped' => false,
                    'label' => 'Degree',
                    'data' => $entity->getDegree(),
                    'required' => false
                ])
                ->add('qualification', TextType::class, [
                    'mapped' => false,
                    'label' => 'Qualification',
                    'data' => $entity->getQualification(),
                    'required' => false
                ])
                ->add('major', TextType::class, [
                    'mapped' => false,
                    'label' => 'Major',
                    'data' => $entity->getMajor(),
                    'required' => false
                ])
                ->add('deputy', TextType::class, [
                    'mapped' => false,
                    'label' => 'Deputy Chairman',
                    'data' => $entity->getDeputy(),
                    'required' => false
                ])
                ->add('minister', TextType::class, [
                    'mapped' => false,
                    'label' => 'Major',
                    'data' => $entity->getMinister(),
                    'required' => false
                ])
                ->add('rector', TextType::class, [
                    'mapped' => false,
                    'label' => 'Major',
                    'data' => $entity->getRector(),
                    'required' => false
                ])
                ->add('date', DateType::class, [
                    'mapped' => false,
                    'label' => 'Date approved',
                    'data' => $entity->getDate(),
                    'input' => 'string',
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => StudyProgram::class,
        ]);
    }

}
