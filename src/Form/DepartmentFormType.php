<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Faculty;
use App\Entity\Teacher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DepartmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:400px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:400px;'], 'label' => 'Name Turkmen'])
                ->add('faculty', EntityType::class, ['class' => Faculty::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Faculty'])
                ->add('departmentHead', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Department Head'])
            ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Graduating' => 1,
                        'Non-graduating' => 0,
                    ],
                    'expanded' => true,
                    'multiple' => false
                ])
            ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ],
                    'expanded' => true,
                    'multiple' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}
