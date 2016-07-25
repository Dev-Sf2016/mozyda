<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class   CompanyDelegateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', TextType::class, array('label'=> 'Delegate Name'))
            ->add('email', EmailType::class, array('label'=>'Email'))
            ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message'=>'Password and confirm password are not same',
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Confirm Password')
                )
            );
            //->add('submit', SubmitType::class, array('label'=>'Save', 'attr'=>array('class'=>'btn btn-custom btn-lg btn-block')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CompanyDelegate',
            //'attr'=>array('novalidate'=>'novalidate'),
        ));
    }

    public function getName(){

        return 'company_delegate_form';
    }

}
?>