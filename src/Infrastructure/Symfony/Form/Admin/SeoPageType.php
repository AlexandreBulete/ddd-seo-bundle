<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoPageResource;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\SeoFieldsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SeoPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('seo', SeoFieldsType::class, ['section' => 'seo', 'label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoPageResource::class,
            'sections' => [
                'seo' => ['label' => 'seo.section.seo'],
            ],
        ]);
    }
}
