<?php

namespace App\Registrar\Form;

use App\Registrar\Entity\RectorOrder;
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

class RectorOrderFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('number', TextType::class, ['attr' => ['style' => 'width:100px;'], 'label' => 'Order number'])
                ->add('title', TextType::class, ['label' => 'Order title'])
                ->add('content', TextareaType::class, ['label' => 'Description'])
                ->add('note', TextareaType::class, ['label' => 'Note', 'required' => false])
                ->add('date', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Order date'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        '' => '0',
                        'Wezipe' => '1',
                        'Rugsat bermek' => '2',
                        'Temmi bermek' => '3',
                        'Okuw sapary' => '4',
                        'Talyplaryň hataryndan çykarmak' => '5',
                        'Ýyldan ýyla geçirmek' => '6',
                        'Familiýasyny üýtgetmek' => '7',
                        'UÝJ ýerleşdirmek' => '8',
                        'Minnetdarlyk bildirmek' => '9',
                        'Nika rugsadyny bermek' => '10',
                        'UÝJ-den çykarmak' => '11',
                        'Talybyň atasynyň adyny üýtgetmek' => '12',
                        'Okuwa kabul etmek hakynda' => '13',
                        'Gulluk iş saparynyň töleglerini geçirmek' => '14',
                        'Sebäpli (akademik) rugsat bermek' => '15',
                        'Diplom işleri tassyklamak' => '16',
                        'Magistrlik işi tassyklamak' => '17',
                        'Tölegsiz okamaga rugsat bermek' => '18',
                        'Okuwa dikeltmek' => '19',
                        'Halypa mugallym bellemek' => '20',
                        'Önümçilik tejribeligine ibermek' => '21',
                    ],
                    'empty_data' => '0',
                    'multiple' => false,
                    'label' => 'Type'
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Disabled' => '0',
                        'Enabled' => '1',
                    ],
                    'expanded' => true,
                    'empty_data' => '0',
                    'multiple' => false,
                    'label' => 'Type'
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
            'data_class' => RectorOrder::class,
            'source_path' => ''
        ]);
    }

}
