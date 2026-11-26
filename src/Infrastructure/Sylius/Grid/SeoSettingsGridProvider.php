<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoSettings\FindSeoSettingsQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoSettingsResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SeoSettingsGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @return PagerfantaInterface<SeoSettingsResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var SeoSettings $settings */
        $settings = $this->queryBus->ask(new FindSeoSettingsQuery());
        $resource = SeoSettingsResource::fromModel($settings);
        $resource->indexingLabel = $this->translator->trans((string) $resource->indexingLabel);

        return new Pagerfanta(new FixedAdapter(1, [$resource]));
    }
}
