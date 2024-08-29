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

class RelativeType extends AbstractType {

//    // ...
//          "dob": "2005",
//      "job": "Aşgabat şäheriniň daşary ýurt dillerine ýöriteleşdirilen x-nji orta mekdebiniň okuwçysy",
//      "pob": "Aşgabat şäheri",
//      "name": "Amanowa Amangul Amanowna",
//      "address": "Aşgabat şäheriniň Berkararlyk etrabynyň A geçelgesiniň 1-nji jaýy",
//      "penalty": "ýok",
//      "relation": "dogany"

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('name', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;height:100px;'],
                    'help' => 'Name',
                    'required' => false
                ])
                ->add('relation', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Relation',
                    'required' => false
                ])
                ->add('dob', TextType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Year of birth',
                    'required' => false
                ])
                ->add('pob', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Place of Birth',
                    'required' => false
                ])
                ->add('job', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Position',
                    'required' => false
                ])
                ->add('address', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Address',
                    'required' => false
                ])
                ->add('penalty', TextareaType::class, [
                    'attr' => ['style' => 'font-size:10px;height:100px;'],
                    'help' => 'Penalty',
                    'required' => false
                ])
        ;
    }

}
