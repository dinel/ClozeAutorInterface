<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Form;

/**
 * Description of ParticipantType
 *
 * @author dinel
 */

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ParticipantType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Name'
            ))
            ->add('email')
            ->add('age')
            ->add('years_edu', TextType::class, array(
                    'label' => 'Years in formal education (The total number of years, '
                                . 'from when you first started school until you finished '
                                . ' your education. Gap years should be excluded)',                
            ));
    }
}
