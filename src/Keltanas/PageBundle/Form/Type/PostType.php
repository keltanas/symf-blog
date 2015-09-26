<?php

namespace Keltanas\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                        'class' => 'mark-down-preview',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Keltanas\PageBundle\Entity\Post'
        ));
    }

    public function getName()
    {
        return 'Keltanas_bundle_pagebundle_posttype';
    }
}
