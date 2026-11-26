<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntries\FindSeoEntriesQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoPageResource;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoResourceFactory;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

/**
 * Every fixed page the application declares, in its order, set or not.
 */
final readonly class SeoPageGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private SeoKinds $kinds,
        private SeoResourceFactory $resources,
    ) {}

    /**
     * @return PagerfantaInterface<SeoPageResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var list<SeoEntry> $entries */
        $entries = $this->queryBus->ask(new FindSeoEntriesQuery(SeoKinds::ROUTE));
        $byRoute = [];
        foreach ($entries as $entry) {
            $byRoute[$entry->target->reference] = $entry->fields();
        }

        $pages = [];
        foreach (array_keys($this->kinds->routes()) as $route) {
            $pages[] = $this->resources->page($route, $byRoute[$route] ?? new SeoFields());
        }

        $pager = new Pagerfanta(new ArrayAdapter($pages));
        $pager->setMaxPerPage(max(1, count($pages)));

        return $pager;
    }
}
