<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoTemplate\FindSeoTemplateQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoResourceFactory;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoTemplateResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

/**
 * One row per kind of page, saved or not.
 */
final readonly class SeoTemplateGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private SeoKinds $kinds,
        private SeoResourceFactory $resources,
    ) {}

    /**
     * @return PagerfantaInterface<SeoTemplateResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        $templates = [];
        foreach ($this->kinds->kinds() as $kind) {
            /** @var SeoTemplate $template */
            $template = $this->queryBus->ask(new FindSeoTemplateQuery($kind));
            $templates[] = $this->resources->template($template);
        }

        $pager = new Pagerfanta(new ArrayAdapter($templates));
        $pager->setMaxPerPage(max(1, count($templates)));

        return $pager;
    }
}
