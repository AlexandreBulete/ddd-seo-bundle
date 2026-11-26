<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoSettingsResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SeoSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('indexing', CheckboxType::class, [
                'label' => 'seo.settings.indexing',
                'required' => false,
                'help' => 'seo.settings.indexing_help',
            ])
            ->add('titlePattern', TextType::class, [
                'label' => 'seo.settings.title_pattern',
                'required' => false,
                'help' => 'seo.settings.title_pattern_help',
            ])
            ->add('robotsRules', TextareaType::class, [
                'label' => 'seo.settings.robots',
                'required' => false,
                'attr' => ['rows' => 8, 'spellcheck' => 'false'],
                'help' => 'seo.settings.robots_help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SeoSettingsResource::class]);
    }
}
