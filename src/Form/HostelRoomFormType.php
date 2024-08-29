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
use App\Entity\HostelRoom;
use App\Entity\Hostel;
use App\Entity\Teacher;

class HostelRoomFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('room_number', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Room Number'])
                ->add('room_name', TextType::class, ['attr' => ['style' => 'width:300px;'], 'label' => 'Room Name'])
                ->add('floor', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Floor'])
                ->add('hostel', EntityType::class, ['class' => Hostel::class,
                    'choice_label' => 'nameEnglish',
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Hostel'])
                ->add('instructor', EntityType::class, ['class' => Teacher::class,
                    'choice_label' => function($entity = null) {
                        return $entity->getFullname();
                    },
                    'choice_value' => 'id',
                    'attr' => ['style' => 'width:300px;'], 'label' => 'Supervising Instructor'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => HostelRoom::class,
        ]);
    }

}
