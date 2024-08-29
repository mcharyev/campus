<?php

namespace App\Form;

use App\Entity\CompetitionResult;
use App\Entity\Competition;
use App\Entity\StudyProgram;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class CompetitionResultFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $entity = $builder->getData();
        //$data = [];
        if ($entity->getData() == null) {
            $data = [
                'systemid' => '',
                'year' => 0,
                'major' => '',
                'livingplace' => '',
                'advisor' => '',
                'advisorposition' => '',
                'note' => ''
            ];
            $entity->setData($data);
            $entity->setResultType(0);
            $entity->setResultLevel(0);
            $entity->setAwardType(0);
            $entity->setViewOrder(1);
        } else {
            $data = $entity->getData();
        }
        $builder
                ->add('studentSearchBox', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Student search box'
                ])
                ->add('person', TextType::class, ['label' => 'Name of Person'])
                ->add('systemid', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'System Id',
                    'data' => $data['systemid'],
                ])
                ->add('competition', EntityType::class, ['class' => Competition::class,
                    'choice_label' => 'nameTurkmen',
                    'choice_value' => 'id',
                    'label' => 'Competition',
                    'data' => $options['competition']
                ])
                ->add('resultType', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'Individual' => '1',
                        'Team' => '2'
                    ],
                    'empty_data' => '1',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Result type'
                ])
                ->add('awardType', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'Medal' => '1',
                        'Diploma' => '2',
                        'Certificate' => '3',
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Result level'
                ])
                ->add('resultLevel', ChoiceType::class, [
                    'choices' => [
                        'None' => '0',
                        'First place - Gold' => '1',
                        'Second place - Silver' => '2',
                        'Third place - Bronze' => '3',
                        'Fourth place' => '4',
                        'Fifth place' => '5',
                        'Honorable mention' => '10',
                    ],
                    'empty_data' => '0',
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Result level'
                ])
                ->add('year', IntegerType::class, [
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'Year',
                    'empty_data' => '0',
                    'data' => $data['year']
                ])
                ->add('major', TextType::class, [
                    'mapped' => false,
                    'label' => 'Major',
                    'data' => $data['major'],
                    'required' => false
                ])
                ->add('livingplace', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Living place',
                    'data' => $data['livingplace'],
                    'required' => false
                ])
                ->add('advisorSearchBox', TextType::class, [
                    'mapped' => false,
                    'label' => 'Advisor search',
                    'required' => false
                ])
                ->add('advisor', TextType::class, [
                    'mapped' => false,
                    'label' => 'Team Advisor',
                    'data' => $data['advisor'],
                    'required' => false
                ])
                ->add('advisorposition', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Advisor position',
                    'data' => $data['advisorposition'],
                    'required' => false
                ])
                ->add('note', TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Additional note',
                    'data' => $data['note'],
                    'required' => false
                ])
                ->add('tags', TextType::class, [
                    'label' => 'Tags',
                    'required' => false
                ])
                ->add('viewOrder', IntegerType::class, [
                    'attr' => ['style' => 'width:100px;'],
                    'label' => 'View order',
                    'empty_data' => '0'
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
            'data_class' => CompetitionResult::class,
            'source_path' => '',
            //'competition_result' => null,
            'competition' => null
        ]);
    }

}
