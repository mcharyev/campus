<?php

namespace App\Form;

use App\Entity\ReferenceDocument;
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

class ReferenceDocumentFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        if (!$entity->getStatus()) {
            $entity->setStatus(1);
        }

        $builder
                ->add('title', TextareaType::class, ['label' => 'Title'])
                ->add('date', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Date'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Başga' => '0',
                        'Türkmenistanyň Kanuny' => '10',
                        'Düzgünnama' => '20',
                        'Tertip' => '30',
                        'Ministrligiň buýrugy' => '40',
                        'Ministrligiň Gözükdirijisi' => '50',
                        'Içerki düzgünnama' => '60',
                        'Içerki buýruk' => '70',
                        'Döwlet standarty' => '80',
                    ],
                    'empty_data' => '0',
                    'multiple' => false,
                    'label' => 'Type'
                ])
                ->add('link', TextType::class, ['label' => 'Link', 'required' => false])
                ->add('authority', TextType::class, ['label' => 'Authority'])
                ->add('note', TextareaType::class, ['label' => 'Note', 'required' => false])
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
            'data_class' => ReferenceDocument::class,
            'source_path' => ''
        ]);
    }

}
