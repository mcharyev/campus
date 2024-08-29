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
use App\Entity\Classroom;
use App\Entity\CampusBuilding;
use App\Entity\ClassroomType;

class ClassroomFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name Turkmen'])
                ->add('capacity', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Capacity'])
                ->add('floor', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Floor'])
                ->add('type', EntityType::class, ['class' => ClassroomType::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Classroom Type'])
                ->add('campusBuilding', EntityType::class, ['class' => CampusBuilding::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Campus Building'])
                ->add('scheduleName', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Schedule name', 'required' => false])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Classroom::class,
        ]);
    }

}
