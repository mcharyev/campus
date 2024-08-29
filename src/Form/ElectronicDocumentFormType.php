<?php

namespace App\Form;

use App\Entity\ElectronicDocument;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\CallbackTransformer;
use App\Enum\DocumentOriginTypeEnum;
use App\Enum\DocumentTypeEnum;
use App\Enum\DocumentEntryTypeEnum;
use App\Enum\DocumentStatusTypeEnum;

class ElectronicDocumentFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $entity = $builder->getData();
        if (!$entity->getEntryType()) {
            $entity->setEntryType($options['entryType']);
            $entity->setDateCreated(new \DateTime());
            $entity->setDateUpdated(new \DateTime());

            $data = [
                'note' => '',
                'files' => '',
                'linked_documents' => '',
                'linked_tasks' => '',
            ];

            $entity->setData($data);
        } else {
            $entity->setDateUpdated(new \DateTime());
        }

        $builder
                ->add('entryType', ChoiceType::class, [
                    'choices' => DocumentEntryTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'data' => $entity->getEntryType(),
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Elektron resminamanyň görnüşi',
                ])
                ->add('originType', ChoiceType::class, [
                    'choices' => DocumentOriginTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Haty ibereniň görnüşi',
                ])
                ->add('documentType', ChoiceType::class, [
                    'choices' => DocumentTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Resminamanyň görnüşi',
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => DocumentStatusTypeEnum::getChoiceTypeArray(),
                    'attr' => ['style' => 'width:200px;'],
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Resminamanyň ýagdaýy',
                ])
                ->add('title', TextareaType::class, [
                    'label' => 'Hatyň ady (temasy)'
                ])
                ->add('originDescription', TextareaType::class, [
                    'label' => 'Hatyň beýany'
                ])
                ->add('destinationDescription', TextareaType::class, [
                    'label' => 'Iberilen ýeriniň beýany'
                ])
                ->add('signatureAuthor', TextareaType::class, [
                    'label' => 'Gol çeken adamyň wezipesi'
                ])
                ->add('dateSent', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Iberilen senesi',
                ])
                ->add('numberSent', TextType::class, [
                    'label' => 'Iberilen belgisi',
                    'attr' => ['style' => 'width:200px;'],
                ])
                ->add('dateReceived', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Giriş senesi',
                ])
                ->add('numberReceived', TextType::class, [
                    'label' => 'Giriş belgisi',
                    'attr' => ['style' => 'width:200px;'],
                ])
                ->add('filingCategory', TextType::class, [
                    'label' => 'Arhiw faýly',
                    'attr' => ['style' => 'width:200px;'],
                    'help' => 'Resminama degişli arhiw faýly. M.ü. 04-01, 04-20'
                ])
                ->add('tags', TextareaType::class, [
                    'label' => 'Bellikler',
                    'help' => 'Özüňize soň gözläp tapmak üçin belgileri arasyna otur goýup ýazyp bilersiňiz: meýletinçilik, ähtnama we ş.m.',
                    'required' => false,
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Goşmaça bellik',
                    'help' => 'Özüňize düşnükli bolar ýaly goşmaça düşündiriş ýazgysy',
                ])
                ->add('dateCreated', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Döredilen senesi',
                    'disabled' => true,
                ])
                ->add('dateUpdated', DateType::class, [
                    'attr' => ['style' => 'width:180px;'],
                    'widget' => 'single_text',
                    'label' => 'Üýtgedilen senesi',
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
            'data_class' => ElectronicDocument::class,
            'entryType' => 1,
            'source_path' => ''
        ]);
    }

}
