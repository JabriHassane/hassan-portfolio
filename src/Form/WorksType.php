<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Works;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null
            ])
            ->add('title', TextType::class, ['required' => false])
            ->add('intro', TextType::class, ['required' => false])
            ->add('description', CKEditorType::class, [
                'config' => [
                    'uiColor' => '00e5ff',
                    'toolbar' => 'full',
                    'required' => true
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Works::class,
        ]);
    }
}
