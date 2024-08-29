<?php

namespace App\Form;

use App\Entity\Freeday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\CallbackTransformer;
use App\Enum\FreedayTypeEnum;

class FreedayFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if (!$entity->getType()) {
            $entity->setDateUpdated(new \DateTime());
        } else {
            $entity->setDateUpdated(new \DateTime());
        }

        $builder
                ->add('title', TextareaType::class, [
                    'label' => 'Title of free day',
                    'help' => 'Enter a not very long title for the freeday'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => FreedayTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:400px;'],
                    'data' => $entity->getType(),
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Type of replacement',
                    'help' => 'No replacement - for free days. Some replacements are for LLD some for Bachelors and Masters'
                ])
                ->add('date', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Date being replaced',
                    'help' => 'Enter the date which is free and being replaced'
                ])
                ->add('new_date', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'New date',
                    'help' => 'Enter the new date where old classes will be scheduled'
                ])
                ->add('session', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                    ],
                    'label' => 'Session',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'help' => 'Enter the old session being replaced. Leave blank if not session replacement.'
                ])
                ->add('new_session', ChoiceType::class, [
                    'attr' => ['style' => 'width:150px;'],
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                    ],
                    'label' => 'New session',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'help' => 'Enter the new session. Leave blank if not session replacement.'
                ])
                ->add('dateUpdated', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Date updated',
                    'disabled' => true,
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
            'data_class' => Freeday::class,
            'source_path' => ''
        ]);
    }

}
