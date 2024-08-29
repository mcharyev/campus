<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WorkPositionType
 *
 * @author nazar
 */
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class WorkPositionType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', TextType::class, [
                'help' => 'Period',
                'required'=> false
            ])
            ->add('position', TextareaType::class, [
                'help' => 'Position',
                'required'=> false
            ])
        ;
    }
}
