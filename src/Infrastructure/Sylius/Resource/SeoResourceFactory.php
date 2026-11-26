<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\SeoFieldsData;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SeoResourceFactory
{
    public function __construct(
        private SeoKinds $kinds,
        private UrlGeneratorInterface $urls,
        private TranslatorInterface $translator,
    ) {}

    public function page(string $route, SeoFields $fields): SeoPageResource
    {
        try {
            $path = $this->urls->generate($route);
        } catch (RoutingException) {
            $path = null;
        }

        return new SeoPageResource($route, $this->translator->trans($this->kinds->routes()[$route] ?? $route), $path, SeoFieldsData::fromFields($fields));
    }

    public function template(SeoTemplate $template): SeoTemplateResource
    {
        $kind = $template->kind;

        return new SeoTemplateResource(
            $kind,
            $this->translator->trans($this->kinds->label($kind)),
            $template->title,
            $template->description,
            $this->kinds->defaultTitle($kind),
            $this->kinds->defaultDescription($kind),
            array_map(fn (string $label): string => $this->translator->trans($label), $this->kinds->variables($kind)),
            $template->updatedAt,
            $this->translator->trans('seo.template.title_fallback'),
            $this->translator->trans('seo.template.description_fallback'),
            $template->structuredData,
        );
    }
}
