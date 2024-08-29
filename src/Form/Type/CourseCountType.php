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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseCountType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('ects', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('total', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('lecture', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('practice', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('Laboratory', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('siw', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('internship', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0
                ])
                ->add('contacthour11', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'CH'
                ])
                ->add('totalhour11', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'TW'
                ])
                ->add('creditshour11', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'TC'
                ])
                ->add('contacthour12', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'CH'
                ])
                ->add('totalhour12', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'TW'
                ])
                ->add('creditshour12', IntegerType::class, [
                    'required' => false,
                    'empty_data' => 0,
                    'label' => 'TC'
                ])

        ;
    }

}
