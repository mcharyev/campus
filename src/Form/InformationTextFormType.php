<?php

namespace App\Form;

use App\Entity\InformationText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class InformationTextFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if (!$entity->getStatus()) {
            $entity->setStatus(1);
        }

        $builder
                ->add('systemId', TextType::class, ['label' => 'System Id'])
                ->add('letterCode', TextType::class, ['label' => 'Letter Code'])
                ->add('title', TextareaType::class, ['label' => 'Title'])
                ->add('content', TextareaType::class, [
                    'label' => 'Content', 
                    'required' => false,
                    'attr' => ['style' => 'height:300px;'],
                    ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Disabled' => '0',
                        'Enabled' => '1',
                    ],
                    'expanded' => true,
                    'empty_data' => '1',
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
            'data_class' => InformationText::class,
            'source_path' => ''
        ]);
    }

}
