<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Twig;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead\FindSeoHeadQuery;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead\SeoHead;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Rendering\SeoRenderer;
use AlexandreBulete\DddSeoBundle\Infrastructure\Rendering\SeoView;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * In the layout's head:
 *
 *     {{ seo_head('review', book.id, {title: book.title, site: site.name}) }}
 *
 * prints the title, description, canonical, robots and share tags. `seo_view`
 * gives the resolved values, for a template that prints them itself.
 */
final class SeoExtension extends AbstractExtension
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly SeoRenderer $renderer,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_view', $this->view(...)),
            new TwigFunction('seo_head', $this->head(...), ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function view(string $kind, string|\Stringable $reference, array $variables = []): SeoView
    {
        $target = new SeoTarget($kind, (string) $reference);
        /** @var SeoHead $head */
        $head = $this->queryBus->ask(new FindSeoHeadQuery($target));

        return $this->renderer->view($head, $kind, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function head(Environment $twig, string $kind, string|\Stringable $reference, array $variables = []): string
    {
        return $twig->render('@DddSeo/head.html.twig', ['seo' => $this->view($kind, $reference, $variables)]);
    }
}
