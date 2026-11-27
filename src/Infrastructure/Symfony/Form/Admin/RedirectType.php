<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RedirectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('source', TextType::class, [
                'section' => 'redirect',
                'label' => 'seo.redirect.source',
                'help' => 'seo.redirect.source_help',
                'attr' => ['maxlength' => 500, 'placeholder' => '/old-address'],
            ])
            ->add('target', TextType::class, [
                'section' => 'redirect',
                'label' => 'seo.redirect.target',
                'help' => 'seo.redirect.target_help',
                'attr' => ['maxlength' => 500],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RedirectResource::class,
            'sections' => [
                'redirect' => ['label' => 'seo.section.redirect'],
            ],
        ]);
    }
}
