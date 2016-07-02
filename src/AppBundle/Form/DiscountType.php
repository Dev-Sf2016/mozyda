<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
//use Symfony\Component\Form\Extension\Core\Type\Date;

class DiscountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array('label'=>'Enter title'))
            ->add('startDate', DateType::class,
                array(
                    'label'=>"Start Date",
                    'widget' =>'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
//                ));
                ))
            ->add('endDate', DateType::class,
                array(
                    "label"=>"End Date",
                    'widget' =>'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],

                ))
            ->add('promotion', FileType::class, array("label" => "Select Image", 'data_class'=>null) )
            //->add('created', 'datetime')
            //->add('updated', 'datetime')
            ->add('submit', SubmitType::class, array('label'=>'Submit'));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Discount',
            'attr'=>array('novalidate'=>'novalidate')
        ));
    }

    public function getName(){
        return 'company_discount_form';
    }
}
