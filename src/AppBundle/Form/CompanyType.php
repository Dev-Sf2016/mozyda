<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', TextType::class, array('label' => 'Company Name'))
            ->add('url', TextType::class, array('label' => 'Website URL'))
            ->add('logo', FileType::class, array('label' => 'Company Logo', 'data_class' => null,
//                'constraints' => array(
//
//                    new Assert\NotBlank()
//
//                )
            ))
            ->add('companyDelegate', CollectionType::class, array('label' => false,
                'entry_options' => array('label' => false),
                'entry_type' => CompanyDelegateType::class,
                'allow_add' => false
            ));
//            ->add('submit', SubmitType::class, array('label'=>'Save', 'attr'=>array('class'=>'btn btn-custom btn-lg btn-block')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Company',
            'attr' => array('novalidate' => 'novalidate'),
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'company_form';
    }

}

?>