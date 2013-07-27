<?php

namespace Keltanas\Bundle\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                    'attr' => [
                        'class' => 'input-xxlarge',
                    ]
                ])
            ->add('contentMd', 'textarea', [
                    'attr' => [
//                        'class' => 'input-xxlarge',
                    ]
                ])
            ->add('contentHtml', 'hidden')
            ->add('tags', 'text', [
                    'attr' => [
                        'class' => 'input-xxlarge',
                    ]
                ])
            ->add('status', 'choice', [
                    'choices' => ['Draft', 'Published'],
                    'empty_value' => 'Choice an status',
                ])
//            ->add('createdAt')
//            ->add('updatedAt')
            ->add('version', 'hidden')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Keltanas\Bundle\PageBundle\Entity\Post'
        ));
    }

    public function getName()
    {
        return 'keltanas_bundle_pagebundle_posttype';
    }
}
