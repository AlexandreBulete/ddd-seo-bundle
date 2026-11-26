<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Title, description, canonical, noindex and share texts of one page. Every
 * text is optional: empty follows the kind's template.
 */
final class SeoFieldsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'seo.field.title',
                'required' => false,
                'help' => 'seo.field.title_help',
                'attr' => ['maxlength' => 120],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'seo.field.description',
                'required' => false,
                'help' => 'seo.field.description_help',
                'attr' => ['rows' => 3, 'maxlength' => 320],
            ])
            ->add('canonical', TextType::class, [
                'label' => 'seo.field.canonical',
                'required' => false,
                'help' => 'seo.field.canonical_help',
            ])
            ->add('noindex', CheckboxType::class, [
                'label' => 'seo.field.noindex',
                'required' => false,
                'help' => 'seo.field.noindex_help',
            ])
            ->add('shareTitle', TextType::class, [
                'label' => 'seo.field.share_title',
                'required' => false,
                'help' => 'seo.field.share_title_help',
                'attr' => ['maxlength' => 120],
            ])
            ->add('shareDescription', TextareaType::class, [
                'label' => 'seo.field.share_description',
                'required' => false,
                'attr' => ['rows' => 2, 'maxlength' => 320],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoFieldsData::class,
            'label' => 'seo.block',
        ]);
    }
}
