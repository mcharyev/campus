<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Schedule;
use App\Entity\ScheduleType;

class ScheduleFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('systemId', TextType::class, ['label' => 'System ID'])
                ->add('letterCode', TextType::class, ['label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['label' => 'Name Turkmen'])
                ->add('type', EntityType::class, ['class' => ScheduleType::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'label' => 'Schedule Type'])
                ->add('startDate')
                ->add('endDate')
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

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
        ]);
    }

}
