<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Application\Query\FindRedirects\FindRedirectsQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

/**
 * By old address; a site keeps a few hundred redirects at most.
 */
final readonly class RedirectGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    /**
     * @return PagerfantaInterface<RedirectResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        $criteria = $parameters->get('criteria', []);
        $search = is_array($criteria) && is_array($criteria['search'] ?? null) && is_string($criteria['search']['value'] ?? null)
            ? $criteria['search']['value']
            : null;

        /** @var list<Redirect> $redirects */
        $redirects = $this->queryBus->ask(new FindRedirectsQuery($search));

        $pager = new Pagerfanta(new ArrayAdapter(array_map(RedirectResource::fromModel(...), $redirects)));
        $limit = $parameters->get('limit', 50);
        $pager->setMaxPerPage(is_numeric($limit) ? max(1, (int) $limit) : 50);
        $page = $parameters->get('page', 1);
        $pager->setCurrentPage(min(max(1, is_numeric($page) ? (int) $page : 1), max(1, $pager->getNbPages())));

        return $pager;
    }
}
