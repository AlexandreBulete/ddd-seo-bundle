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
                'section' => 'site',
                'switch' => true,
                'label' => 'seo.settings.indexing',
                'required' => false,
                'help' => 'seo.settings.indexing_help',
            ])
            ->add('titlePattern', TextType::class, [
                'section' => 'site',
                'label' => 'seo.settings.title_pattern',
                'required' => false,
                'help' => 'seo.settings.title_pattern_help',
            ])
            ->add('robotsRules', TextareaType::class, [
                'section' => 'robots',
                'label' => 'seo.settings.robots',
                'required' => false,
                'attr' => ['rows' => 8, 'spellcheck' => 'false'],
                'help' => 'seo.settings.robots_help',
            ])
            ->add('personName', TextType::class, [
                'section' => 'person',
                'label' => 'seo.settings.person_name',
                'required' => false,
                'help' => 'seo.settings.person_name_help',
            ])
            ->add('personUrl', TextType::class, [
                'section' => 'person',
                'label' => 'seo.settings.person_url',
                'required' => false,
                'help' => 'seo.settings.person_url_help',
            ])
            ->add('personSameAs', TextareaType::class, [
                'section' => 'person',
                'label' => 'seo.settings.person_same_as',
                'required' => false,
                'attr' => ['rows' => 3, 'spellcheck' => 'false'],
                'help' => 'seo.settings.person_same_as_help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoSettingsResource::class,
            'sections' => [
                'site' => ['label' => 'seo.section.site'],
                'person' => ['label' => 'seo.section.person'],
                'robots' => ['label' => 'seo.section.robots', 'column' => 'side'],
            ],
        ]);
    }
}
