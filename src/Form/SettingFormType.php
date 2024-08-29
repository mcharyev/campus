<?php

namespace App\Form;

use App\Entity\Setting;
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

class SettingFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();

        $builder
                ->add('name', TextType::class, ['label' => 'Setting name'])
                ->add('value', TextareaType::class, ['label' => 'Setting value'])
                ->add('description', TextareaType::class, [
                    'label' => 'Setting description',
                    'required' => false,
                    'attr' => ['style' => 'height:300px;'],
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
            'data_class' => Setting::class,
            'source_path' => ''
        ]);
    }

}
