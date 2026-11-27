<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoTemplateResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The patterns of one kind; the help lists the variables it offers and
 * what an empty pattern gives.
 */
final class SeoTemplateType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            $variables = '';
            $defaultTitle = $defaultDescription = null;
            if ($data instanceof SeoTemplateResource) {
                $variables = implode(', ', array_map(static fn (string $name, string $label): string => sprintf('{%s} (%s)', $name, $label), array_keys($data->variables), $data->variables));
                $defaultTitle = $data->defaultTitle;
                $defaultDescription = $data->defaultDescription;
            }
            $empty = fn (?string $default, string $otherwise): string => $default !== null
                ? $this->translator->trans('seo.template.empty_gives', ['%pattern%' => $default])
                : $this->translator->trans($otherwise);

            $event->getForm()
                ->add('title', TextType::class, [
                    'section' => 'template',
                    'label' => 'seo.template.title',
                    'required' => false,
                    'help' => 'seo.template.title_help',
                    'help_translation_parameters' => ['%variables%' => $variables, '%empty%' => $empty($defaultTitle, 'seo.template.title_empty')],
                ])
                ->add('description', TextType::class, [
                    'section' => 'template',
                    'label' => 'seo.template.description',
                    'required' => false,
                    'help' => 'seo.template.description_help',
                    'help_translation_parameters' => ['%variables%' => $variables, '%empty%' => $empty($defaultDescription, 'seo.template.description_empty')],
                ])
                ->add('structuredData', CheckboxType::class, [
                    'section' => 'structured',
                    'switch' => true,
                    'label' => 'seo.template.structured_data',
                    'required' => false,
                    'help' => 'seo.template.structured_data_help',
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoTemplateResource::class,
            'sections' => [
                'template' => ['label' => 'seo.section.template'],
                'structured' => ['label' => 'seo.section.structured', 'column' => 'side'],
            ],
        ]);
    }
}
