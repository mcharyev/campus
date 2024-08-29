<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use App\Entity\Faculty;
use App\Entity\Teacher;

class FacultyFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:400px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:400px;'], 'label' => 'Name Turkmen'])
                ->add('dean', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Dean'])
                ->add('firstDeputyDean', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'First Deputy Dean'])
                ->add('secondDeputyDean', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Second Deputy Dean'])
                ->add('thirdDeputyDean', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Third Deputy Dean'])
                ->add('assistant', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'label' => 'Assistant Dean'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Faculty::class,
        ]);
    }

}
