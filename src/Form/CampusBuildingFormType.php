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

use App\Entity\CampusBuilding;
use App\Entity\CampusLocation;

class CampusBuildingFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('systemId', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'System ID'])
                ->add('letterCode', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Letter Code'])
                ->add('nameEnglish', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name English'])
                ->add('nameTurkmen', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Name Turkmen'])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ]
                ])
                ->add('campusLocation', EntityType::class, ['class' => CampusLocation::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Campus Location'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CampusBuilding::class,
        ]);
    }

}
