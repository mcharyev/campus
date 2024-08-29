<?php

namespace App\Library\Form;

use App\Library\Entity\LibraryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('mainTitle')
            ->add('secondaryTitle')
            ->add('text')
            ->add('writerNumber')
            ->add('year')
            ->add('edition')
            ->add('number')
            ->add('volume')
            ->add('copyNumber')
            ->add('callNumber')
            ->add('callNumberOriginal')
            ->add('string')
            ->add('uok')
            ->add('status')
            ->add('publisher')
            ->add('place')
            ->add('price')
            ->add('invoice')
            ->add('dateUpdated')
            ->add('language')
            ->add('libraryUnit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LibraryItem::class,
        ]);
    }
}
